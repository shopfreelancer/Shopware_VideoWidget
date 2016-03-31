/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * ExtJS Component for Media Widget Plugin
 */
Ext.define('Shopware.apps.Emotion.view.components.SfVideo', {

    /**
     * Extend from the base emotion component.
     */
    extend: 'Shopware.apps.Emotion.view.components.Base',

    /**
     * Set the defined xtype of the component as the new widget alias.
     */
    alias: 'widget.sf-video',

    /**
     * Initialize the component.
     */
    initComponent: function() {
        var me = this;

        // Call the parent component for init.
        me.callParent(arguments);

        me.albumId = me.getAlbumIdValue();

        // Create the media manager field.
        me.createMediaManagerField();

        me.imgPathField = me.getImgPathField();

        // Get the already created hidden input field.
        me.hiddenIdField = me.getHiddenIdField();

        // Create a new fieldset for the custom fields.
        me.createMediaWidgetFieldset();

        // Add the new fieldset to the emotion component.
        me.add(me.widgetFieldset);

        me.createImgPreview();



    },
    /**
     * creates the preview of img file
     * @returns boolean
     */
    createImgPreview: function() {
        var me = this;

        var img_path = me.imgPathField.getValue();

        if(img_path.length === 0){
            return;
        }

        var tpl = new Ext.Template(
            '{literal}'+
            '<div class="img_preview" style="max-width:280px;">'+
            '<img src="{full_img_path}" alt="" style="max-width: 100%;" />'+
            '</div>'+
            '{/literal}'
        );

        Ext.Ajax.request({
            url: '{url controller=SfVideoWidget action="getFullImgPath"}',
            method: 'POST',
            params :{
                img_path : img_path
            },
            success: function(response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    var full_img_path = status.full_img_path;

                    if(typeof(me.imgPreview) !== "undefined"){
                        me.imgPreview.destroy();
                    }
                    me.imgPreview = Ext.create('Ext.Component', {
                        itemId: 'imgPreviewId',
                        tpl: tpl,
                        data: {
                            full_img_path: full_img_path
                        }
                    });

                    me.add(me.imgPreview);
                    return;

                } else {
                    return false;
                }
            }
        });

        return false;
    },


    /**
     * Creates a new custom ExtJS component field.
     * In this example we create a Shopware MediaSelection field.
     *
     * @returns Shopware.form.field.MediaSelection
     */
    createMediaManagerField: function() {
        var me = this;

        return me.mediaManagerField = Ext.create('Shopware.form.field.MediaSelection', {
            buttonText: '{s name=emotion/component/media_widget/media/button_text}Select a file{/s}',
            albumId: parseInt(me.albumId),
            multiSelect     : false,
            listeners: {
                scope: this,
                selectMedia: me.onMediaSelection
            }
        });
    },

    /**
     * Creates a new fieldset for the emotion component configuration.
     *
     * @returns Ext.form.FieldSet
     */
    createMediaWidgetFieldset: function() {
        var me = this;

        return me.widgetFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=emotion/component/media_widget/fieldset/title}Media Widget Settings{/s}',
            layout: 'anchor',
            defaults: { anchor: '100%' },
            items: [
                me.mediaManagerField , me.imgPathField


            ]
        });
    },


    /**
     * Event handler for the media selection field.
     * Will be fired when the user selected some files.
     * Gets the data of the selected files and saves them
     * to the hidden field as a json encoded string.
     *
     * @param field
     * @param records
     */
    onMediaSelection: function(field, records) {
        var me = this,
            id = [],
            imgPath = [];

        Ext.each(records, function(record) {
            id.push(record.data.id);
            imgPath.push(record.data.virtualPath);
        });

        //virtualPath
        me.imgPathField.setValue(imgPath);
        me.hiddenIdField.setValue(id);

        me.createImgPreview();
    },

    /**
     * Search the fieldset of the component
     * for the hidden input field and return it.
     *
     * @returns Ext.form.field.Hidden
     */
    getHiddenIdField: function() {
        var me = this,
            items = me.elementFieldset.items.items,
            storeField;

        Ext.each(items, function(item) {
            if(item.name === 'sf_img_id') {
                storeField = item;
            }
        });

        return storeField;
    },

    getAlbumIdValue : function() {
        var me = this,
            items = me.elementFieldset.items.items,
            storeField;

        Ext.each(items, function(item) {
            if(item.name === 'albumId') {
                storeField = item;
            }
        });
        return storeField.getValue();
    },


    getImgPathField: function() {
        var me = this,
            items = me.elementFieldset.items.items,
            storeField;

        Ext.each(items, function(item) {
            if(item.name === 'sf_img') {
                storeField = item;
            }
        });

        return storeField;
    }
});