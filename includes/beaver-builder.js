(function($) {
  $(function() {

    $( 'body' ).removeClass( 'logichop-render-hide' );

    function toggleLogicHopModules () {
      $('.logichop-bb-hide').toggleClass('logichop-bb-show');
	  }
    FLBuilder.addHook('toggleLogicHopModules', toggleLogicHopModules );

    FLBuilder.addHook( 'showLayoutSettings', function ( e ) {
      setTimeout( function () {
        $('.fl-builder-settings-tabs').append('<a href="#fl-builder-settings-tab-lh" class="logic-hop-fl-settings-tab">Logic Hop</a>');
        $('.fl-builder-settings-fields .fl-nanoscroller-content').append('<div id="fl-builder-settings-section-lh" class="fl-builder-settings-section "><div class="fl-builder-settings-section-content"></div></div>');

        var settings = $('<div style="padding: 0 20px"></div>');

        settings.append('<h3>Toggle Row Display</h3>');
        var rows = $('<ul></ul>');
        $('.lh-row').each( function ( index ) {
          var li = $('<li></li>');
          var name = ( $(this).data('logic-hop-name') ) ? $(this).data('logic-hop-name') : $(this).data('node');
          var $node = $('.fl-node-' + $(this).data('node') );

          li.click( function () {
            if ( $node.hasClass('logichop-bb-hide') ) {
              $node.toggleClass('logichop-bb-show');
            } else {
              $node.toggle();
            }
            if ( $node.is(':visible') ) {
              li.html( name );
            } else {
              li.html( name + ' <em>(Hidden)</em>');
            }
          });
          rows.append(li);

          if ( $node.is(':visible') ) {
            li.html( name );
          } else {
            li.html( name + ' <em>(Hidden)</em>');
          }
        });
        settings.append(rows);

        settings.append('<h3>Toggle Module Display</h3>');
        var modules = $('<ul></ul>');
        $('.lh-module').each( function ( index ) {
          var li = $('<li></li>');
          var name = ( $(this).data('logic-hop-name') ) ? $(this).data('logic-hop-name') : $(this).data('node');
          var $node = $('.fl-node-' + $(this).data('node') );
          li.click( function () {
            if ( $node.hasClass('logichop-bb-hide') ) {
              $node.toggleClass('logichop-bb-show');
            } else {
              $node.toggle();
            }
            if ( $node.is(':visible') ) {
              li.html( name );
            } else {
              li.html( name + ' <em>(Hidden)</em>');
            }
          });
          if ( $node.is(':visible') ) {
            li.html( name );
          } else {
            li.html( name + ' <em>(Hidden)</em>');
          }
          modules.append(li);
        });
        settings.append(modules);

        $('#fl-builder-settings-section-lh .fl-builder-settings-section-content').append(settings);
      }, 250);

    });
  })
})(jQuery);
