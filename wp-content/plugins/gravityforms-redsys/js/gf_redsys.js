function gf_redsys_toggleApiSettings(el){
    if(jQuery(el).length<1){
        return;
    }
    if(jQuery(el).prop('checked')){
        jQuery('#gaddon-setting-row-overrideMode').show();
        jQuery('#gaddon-setting-row-overrideMerchant').show();
        jQuery('#gaddon-setting-row-overrideTerminal').show();
        jQuery('#gaddon-setting-row-overrideSecret').show();
    } else {
        jQuery('#gaddon-setting-row-overrideMode').hide();
        jQuery('#gaddon-setting-row-overrideMerchant').hide();
        jQuery('#gaddon-setting-row-overrideTerminal').hide();
        jQuery('#gaddon-setting-row-overrideSecret').hide();
        jQuery('i').removeClass('icon-check fa-check gf_valid');
    }
}
jQuery(
    function (){
        gf_redsys_toggleApiSettings('#apisettingsenabled');
    }
);