function sortCallback(serializedObjects)
{
    jQuery.ez( 'ezflow2::saveState',
            { serializedObjects:serializedObjects },
            function( data )
            {
                if ( !is_numeric(data.content) )
                {
                    //
                }
                else
                {
                    var url = jQuery.ez.url.replace( 'ezjscore/', 'ezjscore/run/content/view/full/' ) + '2';
                    alert(url);
                    jQuery.post( url, {}, function(){} );
                }
            } );
}

$(document).ready(function(){
  
    //enable sort of blocks
    $('div.zone').Sortable(
        {
            accept :        'sortable-block',
            helperclass :   'sortHelper',
            activeclass :   'sortableactive',
            hoverclass :    'sortablehover',
            onchange :       function(ser)
                             {
                                sortCallback(ser);
                             },
            floats: true
        }
    )
    
});