(function ($, Drupal, once) {
    Drupal.behaviors.cutom_qr_product = {
      attach: function (context, settings) {
        once('cutom_qr_product', 'html', context).forEach( function () {
            var url = $('#url').val();  
            console.log(url);   
            $('#productqr').empty();   
            $('#productqr').css({
                'width' : 350,
                'height' : 350
            });

            $('#productqr').qrcode({width: 350,height: 350,text: url});
        });
      }
    };
  })(jQuery, Drupal, once);