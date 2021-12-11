from selenium import webdriver
from selenium.webdriver.support.ui import Select
from selenium.webdriver.common.keys import Keys
from random import randint, uniform
from webdriver_manager.chrome import ChromeDriverManager

from time import sleep
from datetime import datetime, timedelta, time
from logging.handlers import RotatingFileHandler
from logging import handlers

import pytz
import requests
import logging
import sys
import threading
import json

import string
import secrets
import os


__version__ = 'v1.1'
__location__ = os.path.realpath(os.path.join(os.getcwd(), os.path.dirname(__file__)))
LOGFILE = os.path.join(__location__, 'lernraum.log')
FORMAT = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')

ch = logging.StreamHandler(sys.stdout)
ch.setFormatter(FORMAT)
fh = handlers.RotatingFileHandler(LOGFILE, maxBytes=(1048576*5), backupCount=3)
fh.setFormatter(FORMAT)
logging.basicConfig(level=logging.INFO, handlers=[ch, fh])

_TESTING = False
_BOOKSLOT = True
_DISCORD = False
disableLogin = False
aSlotWillBeAvailableSoonDespiteFullyBooked = False # use this ONLY if you know someone who wants to cancel a slot
refreshRate = 1

# because server time may be different:
tz = pytz.timezone('Europe/Berlin')

# remove this if you wish to disable discord webhook logging
webhookUrl = # discord webhook here
bookingUrl = 'https://buchung.hsz.rwth-aachen.de/angebote/aktueller_zeitraum/_Lernraumbuchung.html'
userFile = 'users.json'
slotsFile = 'slots.json'

#check os
if os.environ['PROCESSOR_ARCHITECTURE'] == 'heroku':
    GOOGLE_CHROME_BIN = os.environ.get('GOOGLE_CHROME_BIN', 'chromedriver')
    CHROMEDRIVER_PATH = '/app/.chromedriver/bin/chromedriver'
else:
    #CHROMEDRIVER_PATH = os.path.join(__location__, 'chromedriver.exe') if you want to use local chromedriver
    CHROMEDRIVER_PATH = ChromeDriverManager().install() # automatically updates local chromedriver, if outdated

# tell me you have ocd without telling me you have ocd:
def discordCheck(bool): 
    if bool:
        return ':white_check_mark:'
    else:
        return ':x:'

def sendDiscord(msg):
    if not _DISCORD: return 0
    now = datetime.now(tz)
    data = {
        "content" : '{} - {}'.format(now.replace(microsecond=0, tzinfo=None), msg),
        'username' : 'Lernraum Bratan'
    }
    result = requests.post(webhookUrl, json = data)

    try:
        result.raise_for_status()
    except requests.exceptions.HTTPError as err:
        return err
    else:
        return result.status_code

def readSlots():        
    with open(os.path.join(__location__, slotsFile), 'r', encoding='utf-8') as fd:
        data=fd.read()
    return json.loads(data)
    
# ================================== get users
def readUsers():        
    with open(os.path.join(__location__, userFile), 'r', encoding='utf-8') as fd:
        data=fd.read()
    return json.loads(data)

def updateUsers(data):
    with open(os.path.join(__location__, userFile), 'w', encoding='utf-8') as fd:
        json.dump(data, fd, indent=4)

# finds hidden input by name and returns value
def getValueByName(driver, name):
    elements = driver.find_elements_by_name(name)
    if bool(elements):
        value = elements[0].get_attribute('value')
        return value
    else:
        return 0
        
# check css selector in browser dev console using document.querySelector('.bs_fval_iban')        
def elementExistsByCss(driver, element):
    x = driver.find_elements_by_css_selector(element)
    if x:
        return len(x)
    else:
        return False        

def generatePassword():
    logging.info('Generating Password...')
    characters = string.ascii_letters + string.digits + string.punctuation
    while True:
        password = ''.join(secrets.choice(characters) for i in range(16))
        if (any(c.islower() for c in password) and any(c.isupper() for c in password) and any(not c.isalnum() for c in password)):
            break
    return password

# ================================== booking logic
def getValidForm(driver, roomId, bookingUrl):
    driver.get(bookingUrl)
    input1 = driver.find_elements_by_name(roomId) 
    
    count = 0
    if input1 == None or len(input1) == 0:
        count += 1
        while True:
            count += 1
            input1 = driver.find_elements_by_name(roomId) 
            if input1 == None or len(input1) == 0:
                logging.info('Noch keine Buchung verfügbar, %i. Durchlauf...' % count)
                sleep(refreshRate)
                driver.refresh()
            else: 
                break
    input1[0].click()
    
    window = driver.window_handles[1]
    driver.switch_to.window(window)

    
    if _TESTING or aSlotWillBeAvailableSoonDespiteFullyBooked: 
        tomorrowDate = datetime.now(tz) # + timedelta(1) # wont work on sundays
    else:
        tomorrowDate = datetime.now(tz) + timedelta(1)
        
    bookingDate = 'BS_Termin_' + tomorrowDate.strftime('%Y-%m-%d')
    logging.info("booking date: " + str(tomorrowDate))

    count = 0
    timeout = 120 # 2 min @ 1 sec refresh rate
    while count < timeout:
        count = count + 1
        input2 = driver.find_elements_by_name(bookingDate)
        if len(input2) == 0 or input2 == None:
            logging.info('Buchung wurde noch nicht freigegeben, %i. Durchlauf...' % count)
            sleep(refreshRate)
            driver.refresh()
        elif input2[0].get_attribute('value') == 'buchen':
            logging.info('Link gefunden.')
            input2[0].click()
            return 1
        elif input2[0].get_attribute('value') == 'Warteliste':
            if aSlotWillBeAvailableSoonDespiteFullyBooked:
                logging.info('Slot ist zwar ausgebucht, aber es sollte gleich etwas frei werden. %i. Durchlauf...' % count)
                sleep(refreshRate)
                driver.refresh()
            else:
                return 0
                
    logging.error('Timeout, mehr als %i Versuche.' % timeout)
    return 0
        

# returns 0 if either: no password is set or nothing is returned from server or login is disabled by user
# from form to confirmation page
def postLoginForm(driver, userData, fid):
    if 'password' not in userData or userData['password'] == '' or disableLogin:
        return 0

    logging.info('Found a valid password, proceeding to login...')
    driver.find_element_by_class_name('bs_arrow').click()

    userInput = driver.find_elements_by_name('pw_pwd_' + fid)
    userInput[0].send_keys(userData['password'])

    userInput = driver.find_elements_by_name('pw_email')
    userInput[0].send_keys(userData['email'])
    #driver.execute_script("arguments[0].setAttribute('value',arguments[1])", userInput[0], userData[i])
    
    submit = driver.find_element_by_css_selector('div.bs_right > input')
    submit.click() # filled out form
    logging.info('Login form sent!')
    
    if driver.find_elements_by_name('name')[0].get_attribute('value') == '':
        logging.warning('Login is incorrect or doesnt exist')
        return 0

    if elementExistsByCss(driver, '.bs_fval_iban') > 0:
        iban = driver.find_elements_by_name('iban')
        iban[0].send_keys(userData['iban'])
        
    if elementExistsByCss(driver, 'textarea.bs_form_field') > 0:
        bemerkung = driver.find_elements_by_name('bemerkung')
        bemerkung[0].send_keys(userData['bemerkung'])               
        
    checkBox = driver.find_element_by_name('tnbed')
    checkBox.click()
    
    submit = driver.find_element_by_css_selector('#bs_foot > div.bs_form_row > div.bs_right > input')
    submit.click() # Zur confirmation page
    logging.info('Login exists and filled form has been sent...')
    return


def postForm(driver, userData):
    for i in userData:
        userInput = driver.find_elements_by_name(i)
        if i == 'sex':
            for j in userInput:
                if j.get_attribute('value') == userData[i]:
                    j.click()
                    break
        elif i == 'statusorig':
            Select(userInput[0]).select_by_value(userData[i])
        elif len(userInput) == 1:
            driver.execute_script("arguments[0].setAttribute('value',arguments[1])",userInput[0], userData[i])

    if elementExistsByCss(driver, '.bs_fval_iban') > 0:
        driver.find_elements_by_name('iban')
        driver.send_keys(userData['iban'])
        
    if elementExistsByCss(driver, 'textarea.bs_form_field') > 0:
        driver.find_elements_by_name('bemerkung')
        driver.send_keys(userData['bemerkung'])        

    checkBox = driver.find_element_by_name('tnbed')
    checkBox.click()

    submit = driver.find_element_by_css_selector('#bs_foot > div.bs_form_row > div.bs_right > input')
     
    sleep(5 + uniform(0., .2)) # warten bis js evaluiert
    submit.click() # Zur confirmation page
    return


def handleBrudi(driver, brudi, roomId):
    logging.info('Brudi: {} {}'.format(brudi['vorname'], brudi['name']))

    # get form
    try:
        validBookingLink = getValidForm(driver, roomId, bookingUrl)
        
        if validBookingLink == 0: # Check current status
            logging.error('Leider zu spät bratan, alle Plätze weg :(')
            sendDiscord(':slight_frown: Leider zu spät bratan, alle Plätze weg')        
            driver.quit()
            return
    except Exception as err:
        logging.error(err)
        sendDiscord(':no_entry_sign: An error has occurred: {}'.format(err))
        return

    sendDiscord(':hourglass_flowing_sand: Slots available. Booking now...')
    fid = getValueByName(driver, 'fid')

    loginAvailable = postLoginForm(driver, brudi, fid)
    if loginAvailable == 0:
        logging.info('No login available, setting password to book quicker next time...')
        postForm(driver, brudi) # bucht normal
        
        # IF IT ERRORS SOMEWHERE HERE, CHECK YOUR USERDATA
        #setting new password
        set_passwd = driver.find_element_by_name('pw_newpw_' + fid)
        if 'password' not in brudi or brudi['password'] == '': # try to use password set by user in file
            brudi['password'] = generatePassword()
        driver.execute_script("arguments[0].setAttribute('value',arguments[1])",set_passwd, brudi['password'])
        
    # not entirely sure if this is reliable
    if elementExistsByCss(driver, '.bs_form_field') > 1: # 2 textfelder: password und email
        email_confirm = driver.find_element_by_name('email_check_' + fid) # lets hope this doesnt error
        email_confirm.send_keys(brudi['email'])

    # next page, final click
    submit2 = driver.find_element_by_css_selector('#bs_foot > div.bs_form_row > div.bs_right > input')

    if _BOOKSLOT:
        submit2.click()
        logging.info('Abgesendet! Inshallah, so Gott will, hast du Platz bekommen')
    else:
        logging.info('---- DIDNT BOOK ----')
        sendDiscord(':x: Enable _BOOKSLOT to enable booking.')        
    
    # attempting to get definitive result
    while True:
        if not _BOOKSLOT:
            break
        elif driver.title == 'Bestätigung':
            logging.info('Platz bestätigt!: ' + str(brudi['email']))
            sendDiscord(':white_check_mark: {} booked for {}'.format(slots[roomId]['name'], brudi['vorname']))            
            break
        elif driver.title != 'Anmeldung':
            logging.info('Etwas ist schiefgelaufen: ' + driver.title)
            sendDiscord(':x: Failed to book for {}'.format(brudi['vorname']))         
            break

    logging.info("Thread für: " + brudi['email'] + " beendet sich.")
    driver.quit()
    return


def main(roomId):
    brudis = readUsers()
    options = webdriver.ChromeOptions()
    options.add_argument('headless')
    options.add_experimental_option('excludeSwitches', ['enable-logging']) # according to stackoverflow this might be windows specific

    if os.environ['PROCESSOR_ARCHITECTURE'] == 'heroku':
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-gpu')
        options.binary_location = GOOGLE_CHROME_BIN
        options.add_argument('--disable-dev-shm-usage')
    
    threads = []
    for i in brudis:
        if i['matnr'] in slots[roomId]['bookers']:
            userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
            options.add_argument(f'--user-agent={userAgent}')
            logging.info('Ich klär Bibplatz für dich {} abi, keine Sorge'.format(i['vorname']))
            driver = webdriver.Chrome(executable_path=CHROMEDRIVER_PATH, options=options)

            t = threading.Thread(target=handleBrudi, args=(driver, i, roomId))
            threads.append(t)
            
    logging.info('Versuche einen freien Platz zu bekommen...')    
    
    for t in threads:
        t.start()
    
    for t in threads:
        t.join() # wait til threads have finished

    sendDiscord(':checkered_flag: Finished booking {}'.format(slots[roomId]['name']))
    logging.info('Updating users.json...')
    updateUsers(brudis)
    return
    

if __name__ == "__main__":
    logging.info('============ BEREIT ZU DRIBBLEN ============')
    slots = readSlots()
    n = len(readUsers())
    sendDiscord(':soccer: ** Dribbler Online **')
    sendDiscord(':gear: **Testing**: {}, **BookSlot**: {}, **OsArch**: {}, **Version**: {}, **Refresh Rate**: {}, **# of Users**: {}'.format(discordCheck(_TESTING), discordCheck(_BOOKSLOT), os.environ['PROCESSOR_ARCHITECTURE'], __version__, refreshRate, n))

    now = datetime.now(tz)
    for slot in slots:
        slotData = slots[slot]
        runAtData = slotData['run_at']
        run_at = now.replace(day=now.day, hour=runAtData['hour'], minute=runAtData['minute'], second=runAtData['second'], microsecond=runAtData['microsecond'])
        delay = (run_at - now).total_seconds()
        if aSlotWillBeAvailableSoonDespiteFullyBooked:
            delay = 0
        elif delay < 0:
            run_at += timedelta(hours=24)
            delay = (run_at - now).total_seconds()
        sceduleStr = 'Scheduling {0} to run at {1} (UTC +2).'.format(slotData['name'], run_at.replace(microsecond=0, tzinfo=None))
        logging.info(sceduleStr)
        sendDiscord(':timer: {}'.format(sceduleStr))
        threading.Timer(delay, main, args=(slot, )).start()