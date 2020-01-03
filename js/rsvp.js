jQuery(document).ready(function($) {
    $('.create-post-on').click(function(e) {
      var sb_event_id = $("#test").text();
      var sb_rsvp_guest = $("#additional_guests").find('option:selected').text();
      var sb_CA_part = $("input[name='part']:checked"). val();
      
        e.preventDefault();

      $.ajax({
          url: scoutbookrsvp.ajax_url,
          type: 'post',
          data: {
            action: 'add_rsvp',
            guest_count: sb_rsvp_guest,
            rsvp_event: sb_event_id,
            part: sb_CA_part,

          },
          success: function( data ) {
                 $('.create-post-on').hide();
                 $('.create-post-off').show();
                 $('.delete-post-on').show();
                 $('.delete-post-off').hide();
                 $('.rsvp_status').text("You are RSVP'ed for " + sb_rsvp_guest);
          },
          error: function( data) {
            console.log(data);
          }
        });       
    });
  
   $('.delete-post-on').click(function(e){
      var sb_event_id = $("#test").text();
          e.preventDefault();
          $.ajax({
          url: scoutbookrsvp.ajax_url,
          type: 'post',
          data: {
            action: 'delete_rsvp',
            rsvp_event: sb_event_id,
          },
          success: function( data ) {
               $('.create-post-on').show();
               $('.create-post-off').hide();
               $('.delete-post-on').hide();
               $('.delete-post-off').show();
               $('.rsvp_status').text("You are not RSVP'ed ");
          },
          error: function( data ) {
            console.log(data);
          }
        }); 
   })
});
