/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$(document).ready(function ($) {
//Adds details div used to display user specific data
    $('#user_profile_businessType_0')
        .parent()
        .parent()
        .after('<div id="details"></div>')
    ;
//Adds specific fields for Individual
    $('#user_profile_businessType_0').click(function() {
        removeFields();
    });
//Adds specific fields for Association
    $('#user_profile_businessType_1').click(function() {
        removeFields();
        displayAssociationBusiness();
    });
//Adds specific fields for Business
    $('#user_profile_businessType_2').click(function() {
        removeFields();
        displayAssociationBusiness();
        displayBusiness();
    });
//Remove fields
    function removeFields() {
        $('#details')
            .empty()
        ;

        var fields = [
            'user_profile_businessName',
            'user_profile_businessAddress',
            'user_profile_businessAddress2',
            'user_profile_businessPostal',
            'user_profile_businessTown',
            'user_profile_businessCountry',
            'user_profile_businessSiret',
            'user_profile_businessTva'
        ];

        fields.forEach(function(field) {
            $('#' + field)
                .parent()
                .empty()
            ;
        });
    }
//Return formatted field
    function displayField(field) {
        if (field != 'user_profile_businessAddress2') {
            var requiredLabel = ' required';
            var requiredForm = ' required="required"';
        } else {
            var requiredLabel = '';
            var requiredForm = '';
        }

        return '<div class="form-group">\
                    <label for="' + field + '" class="control-label' + requiredLabel + '">' + userBusinessTrans[field] + '</label>\
                    <input type="text" id="' + field + '" name="user_profile[' + field.replace('user_profile_', '') + ']" placeholder="' + userBusinessTrans[field] + '" value="' + userBusinessValues[field] + '" class="form-control"' +  requiredForm + '" />\
                </div>';
    }
//Displays specifics fields for Association/Business
    function displayAssociationBusiness() {
        var fields = [
            'user_profile_businessName',
            'user_profile_businessAddress',
            'user_profile_businessAddress2',
            'user_profile_businessPostal',
            'user_profile_businessTown',
            'user_profile_businessCountry'
        ];

        fields.forEach(function(field) {
            $('#details')
                .append(displayField(field))
            ;
        });
    }
//Displays specifics fields for Business
    function displayBusiness() {
        var fields = [
            'user_profile_businessSiret',
            'user_profile_businessTva'
        ];

        fields.forEach(function(field) {
            $('#details')
                .append(displayField(field))
            ;
        });
    }
});