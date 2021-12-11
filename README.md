# dribbler
Booking slots for RWTH study rooms / sport events using python and an optional PHP user interface for managing slots

How To Use:
- Fill in your user information in users.json
- If you have a password set to login during booking, enter the password in the appropriate field. If you want to have a password set, you may choose one by yourself and enter it in the appropriate field. Leave the field empty and it will set a random password automatically for you.
- Fill in your immatriculation number in slots.json at "bookers"
- If you want logging messages sent to your discord server, setup a discord webhook and paste the link at the appropriate variable at lernraum.py
- Start lernraum.py: The next available slot will be booked for you. Keep the script running on your computer or run the script on a server if you want continuous slot booking without interruptions.
