@extends ('orbit.master')

@section ('content')
<!-- SC1 -->
<!-- Result 1 -->
<div class="row">
  <div class="col-xs-10 col-xs-offset-1">
    @include ('referral.create.multiple-results')
  </div> <!-- Col Close -->
</div> <!-- Row Close -->

    @include ('referral.create.booking')
  <!-- Modal Start -->     
  <div class="modal fade" id="SelectMatch" tabindex="-1" role="dialog" aria-labelledby="SelectMatchLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="SelectMatchLabel">Send Referral</h4>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <div class="container-fluid">
          <!-- Top -->
            <div id="result-step-1">
              <div class="row">
                <!-- Logo -->
                <div class="col-xs-6 col-sm-4">
                  <img src="#" class="img-responsive img-thumbnail center-block">
                </div>
                <!-- Service & SP -->
                <div class="col-xs-6 col-sm-8">
                  <h3 class="service-name"><strong>Homeless Law Service</strong></h3>
                  <h4 class="service-provider-name">Justice Connect</h4>
                  <p>Send the client the contact details of this service and a record of this Orbit referral by Email, SMS or both with the form below.</p>
                </div>
              </div>

              <!-- Start Form --> 
              <div class="row">
                <div class="col-xs-12">
                  <h3><small>Send to client by email:</small></h3>
                  <!-- Send to Client form -->
                  <form>
                    <!-- Client Name -->
                    <div class="form-group">
                    <!-- Email Address -->
                    <div class="form-group">
                      <label class="sr-only" for="Email">Email Address</label>
                      <input type="email" class="form-control" id="Email" placeholder="Client Email Address">                        
                      <div class="col-xs-11 col-xs-offset-1">
                        <div class="form-group">
                          <div class="checkbox">
                            <label>
                              <input type="checkbox" id="safeEmail"> This email address is safe to contact                                  
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <h3><small>Send to client by SMS:</small></h3>
                    <!-- Phone Number -->
                    <div class="form-group">
                      <label class="sr-only" for="Phone">Phone Number</label>
                      <input type="tel" class="form-control" id="Phone" placeholder="Client Phone Number">                            
                      <div class="col-xs-11 col-xs-offset-1">
                        <div class="form-group">
                          <div class="checkbox">
                            <label>
                              <input type="checkbox" id="safePhone"> This phone number is safe to text                          
                            </label>
                          </div>  
                        </div>
                      </div>
                    </div>
                    <!-- Button -->
                    <div class="col-xs-6 col-xs-offset-3"><br>                        
                        <!-- Trigger Modal -->
                        <button type="button" class="btn green-jungle btn-block btn-lg pull-right" id="send-client">Send to Client</button><br><!-- Trigger Modal -->
                    </div><!-- End Form -->
                  </form>
                </div>
              </div>
            </div>
          </div> <!-- Modal Container -->
            
            <div class="row" id="result-step-2" style="display:none">
              <div class="col-xs-12 text-center">
                <p style="font-size: 126px;"><i class="fa fa-check-circle" style="color: #5cb85c;background-color: #fff;"></i></p>
                <h3><strong>Referral Sent to Client</strong></h3>
                <h3><strong>ID: #</strong><span id="referral_id"></span></h3><br>
                <button type="button" class="btn default btn-outline btn-lg" data-toggle="modal" data-target="#SelectMatch" id="close-modal"><span>View Results</span></button>
                <button type="button" class="btn green-jungle btn-lg" onClick="window.location='/referral';">Return to Dashboard</button><br><br><br><br>
              </div>
            </div>
        </div> <!-- Modal Body Close-->
      </div><!-- Modal Content Close-->
    </div><!-- Modal Dialogue Close-->
  </div><!-- Modal Fade Close-->
@endsection

@section('scripts')
@endsection

@section('inline-scripts')
  var service_id = 0;
  $(document).ready(function() {

    $('.open-booking').on( "click", function(){

      var service_card = $( this ).closest(".service-card");
      var sv_id = $( service_card ).attr("id");
      var booking_ids = $( this ).attr("id").split('-');
      var sp_id = booking_ids[2];
      var booking_id = booking_ids[0];
      var booking_interpretor_id = booking_ids[1];
      
      $('#service_provider_id').attr("disabled", "disabled");
      $('#service_provider_id option[value="' + sp_id + '"]').prop("selected", "selected").change();
      $('#sp_services').attr("disabled", "disabled");

      $(".booking-area").addClass("hidden");      
      setTimeout(function(){
          $('#sp_services option[value="' + booking_id + '-' + booking_interpretor_id + '"]').prop("selected", "selected").change();
          $(".booking-area").hide().removeClass("hidden").fadeIn();
      }, 2500);
    });

    $('.open-modal').on( "click", function(){    
      var service_card = $( this ).closest(".service-card");
      var service_provider_name = $(service_card).find(".service-provider-name").text();
      var service_name = $(service_card).find(".service-name").text();
      var image_path = $(service_card).find("img").attr("src");
      service_id = $( service_card ).attr('id');

      var modal = $("#SelectMatch");
      $(modal).find(".service-provider-name").text(service_provider_name);
      $(modal).find(".service-name").text(service_name);
      $(modal).find("img").attr("src", image_path);
    });


    $( "#close-modal, .close" ).on( "click", function() {
      $("#result-step-1").show();
      $("#result-step-2").hide();
      $("#service_provider_id option").prop("selected", false);
    });

    $('#send-client').on( "click", function(){
      var phone = $("#Phone").val();
      var email = $("#Email").val();
      var safe_phone = 0;
      var safe_email = 0;
      if( $("#safeEmail").is(':checked') )
      {
        safe_email = 1;
      }
      if( $("#safePhone").is(':checked') )
      {
        safe_phone = 1;
      }

      if( isEmail( email ) || phone != '' )
      {
        $.ajax({
          headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          method: "POST",
          url: "/referral",
          data: { 
                  Mobile: phone, 
                  Email: email,
                  SafeMobile: safe_phone,
                  SafeEmail: safe_email,
                  ServiceId: service_id
                }
        })
          .done(function( msg ) {
            console.log( msg );
            $("#referral_id").html(msg.data);
            $("#result-step-1").hide();
            $("#result-step-2").show();
          });
      } 
      else {
        if( safeEmail && !isEmail( email ) )
        {
          alert("Please enter a valid email address");
        }
        if ( safePhone && phone == '' )
        {
          alert("Please enter a mobile number");
        }
        alert( "Please provide an Email and/or a mobile number." );
      }

    });

    $('form').submit(function(e) {
        $(':disabled').each(function(e) {
            $(this).removeAttr('disabled');
        })
    });
  });  

  function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }
@endsection