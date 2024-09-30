/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * Index Field JS
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/rpqa99
 * @copyright Copyright (c) 2022 rpqa99
 */

$(document).ready(function() {
var menuList = $('#menu-list'),
    menuListItemsCount = menuList.find('tbody tr').length;

    $('#menu-list .delete').each(function(){

        var menu = $(this),
            menuParent = menu.parent().parent(),
            menuID = menuParent.attr('data-id'),
            menuName = menuParent.attr('data-name');

        $(this).on('click',function() {
            if (confirm(Craft.t('simplerpmenu', 'Are you sure you want to delete the "{menuName}" menu?', {menuName: menuName }))) {
                var data = {
                    menuID: menuID
                }
                // Add the CSRF Token
                data[csrfTokenName] = csrfTokenValue;

                $.post(siteUrl +'/simplerpmenu/delete-menu', data, null, 'json')
                    .done(function( data ) {
                        if (data.success) {
                            Craft.cp.displayNotice(Craft.t('simplerpmenu', 'Menu successfully deleted.'));
                            menuParent.remove();
                            
                            var menuListItems = menuList.find('tbody tr'),
                            menuListItemsCount = menuListItems.length;
                            
                            if ( menuListItemsCount == 0 )
                            {
                                menuList.remove();
                                $('#menu-none').removeClass('hidden');
                            }
                        }
                        else Craft.cp.displayError(Craft.t('simplerpmenu','Menu was not deleted. Please try again.'));
                    });
            }
        });
    });
});