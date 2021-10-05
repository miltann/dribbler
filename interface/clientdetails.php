<?php
require 'header.php';

if(!isset($_SESSION['matnr'])) {
    redirect('login.php');
}

$userData = $db->getUserData($_SESSION['matnr']);
$_SESSION['id'] = $userData['id'];
?>

      <div class="row">
        <div class="col-2">
          <div class="sidebar-section sticky-top">
            <div class="sidebar-item">
              <div class="sidebar-content">
               <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                  <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Buchungsverlauf</a>
                  <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#slots" role="tab" aria-controls="v-pills-profile" aria-selected="false">Slots</a>
                  <a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Einstellungen</a>
                </div>                
              </div>
            </div>
          </div>
        </div>
      
      
        <div class="col-9">
          <div class="content-section">
            
<div class="card">

  <div class="card-body">
    <form action="actions/update-userinfo.php" method="post">
            <div class="form-group row">
                    <div class="col-md-6 mb-2">
                    <label class="col-form-label">Vorname</label>
                    <input class="form-control" type="text" name="vorname" value="<?php echo $userData['vorname']; ?>">
                    </div>

                    <div class="col-md-6 mb-2">
                    <label class="col-form-label">Nachname</label>
                    <input class="form-control" type="text" name="name" value="<?php echo $userData['name']; ?>">
                    </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                <label class="col-form-label">Straße</label>
                <input type="text" class="form-control" name="strasse" value="<?php echo $userData['strasse']; ?>">
                </div>
                
                <div class="col-md-6">
                <label class="col-form-label">PLZ Ort</label>
                <input class="form-control" type="text" name="ort" value="<?php echo $userData['ort']; ?>">
                </div>
            </div>        
            <div class="form-group row">
                <div class="col">
                <label class="col-form-label">Telefonnummer</label>                
                <input class="form-control" type="text" name="telefon" value="<?php echo $userData['telefon']; ?>">
                </div>
            </div>              
            <div class="form-group row">
                <div class="col-md-6">
                <label class="col-form-label">Matrikelnummer</label>                
                <input class="form-control" type="text" name="matnr" value="<?php echo $userData['matnr']; ?>">
                </div>
                <div class="col-md-6">
                <label class="col-form-label">Status</label>                
                    <select class="form-select" name="statusorig">
                      <option value="FH">eine andere minderwertige Uni</option>
                      <option value="S-RWTH" <?php if ($userData['statusorig'] == 'S-RWTH') { echo "selected";}?>>RWTH Student</option>
                    </select>
                </div>
            </div>             
      
            <div class="form-group row">
                <div class="col">
                <label class="col-form-label">E-Mail</label>                
                <input class="form-control" type="text" name="email" value="<?php echo $userData['email']; ?>">
                </div>
            </div>        
            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">Passwort</label>
                    <input class="form-control" type="text" name="password" value="<?php echo $userData['password']; ?>">
                    <small class="text-muted">Das Passwort muss mindestens 8 Zeichen lang sein, eine Ziffer und ein Sonderzeichen enthalten. Um dir eins generieren zu lassen, lass dieses Feld frei.</small>
                </div>
            </div>
            <div class="form-group row">
                <div class="d-flex flex-column mt-3">
                    <button class="btn btn-primary btn-lg btn-block" type="submit" name="update-userinfo">Speichern</button>
                </div>
            </div>
    </form>
    
</div>
</div>            
<hr id="slots">
<div class="card mb-5">

  <div class="card-body">
  
<div class="row row-cols-1 row-cols-md-2 g-4">

<!-- Room Cards -->
<?php
$rooms = $db->getDistinctRooms();
foreach($rooms as $room):
?>
  <div class="col d-flex align-items-stretch">
    <div class="card w-100">

      <img src="bib2.jpeg" class="card-img-top" alt="...">

      <div class="card-body">
      <h5 class="card-title"><?php echo $room['room_name'];?></h5>
      

<?php
    $slots = $db->getSlotsFromRoom($room['room_name']);
    foreach($slots as $slot):
        $opens_at = substr($slot['opens_at'], 0,-3);
        $closes_at = substr($slot['closes_at'], 0,-3);
        $timeSlot = "{$opens_at} - {$closes_at}";
        
        # If no records exist of a booking for specific slot and user, disable switch
        $status = $db->getBookingStatusForSlot($slot['bs_kursId'], $userData['matnr']);
        $len = count($status);
        $checked = "";
        if ($len > 0) {
            $checked = "checked";
        }
        
?>
    <form action="actions/update-bookings.php" name="bookingForm" method="post"> 
      <div class="d-flex justify-content-between align-items-end mb-1">
        <input type="hidden" name="kursId" value="<?php echo $slot['bs_kursId']; ?>" />
        <input type="hidden" name="matnr" value="<?php echo $userData['matnr']; ?>" />
        <h6 class="card-subtitle text-muted"><?php echo $timeSlot;?></h6>
        <div class="form-switch">
            <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" name="enableBooking" onchange="this.form.submit()" <?php echo $checked; ?>>
        </div>
      </div>
  </form>
<?php
    endforeach;
?>      
        
        
      </div>
    </div>
  </div>
<?php  
endforeach;
?>
  

</div>

  </div>
</div>


            
          </div>
        </div>

        
      </div>


</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="script.js"></script>
<html>