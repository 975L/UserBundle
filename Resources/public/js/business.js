/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/*global userBusinessTrans, userBusinessValues*/

//Remove fields
function removeFields() {
    $("#details")
        .empty()
    ;

    let fields = [
        "user_profile_businessName",
        "user_profile_businessAddress",
        "user_profile_businessAddress2",
        "user_profile_businessPostal",
        "user_profile_businessTown",
        "user_profile_businessCountry",
        "user_profile_businessSiret",
        "user_profile_businessVat"
    ];

    fields.forEach(function(field) {
        $("#" + field)
            .closest(".form-group")
            .hide()
            .empty()
        ;
    });
}

//Returns formatted field
function displayField(field) {
    let requiredLabel = "";
    let requiredForm = "";
    if (field !== "user_profile_businessAddress2") {
        requiredLabel = " required";
        requiredForm = " required=\"required\"";
    }

    //bootstrap_3_horizontal_layout
    if ($("#formLayout").val() === "bootstrap_3_horizontal_layout") {
        return "<div class=\"form-group\">\
                    <label for=\"" + field + "\" class=\"col-sm-2 control-label" + requiredLabel + "\">" + userBusinessTrans[field] + "</label>\
                    <div class=\"col-sm-10\">\
                        <input type=\"text\" id=\"" + field + "\" name=\"user_profile[" + field.replace("user_profile_", "") + "]\" placeholder=\"" + userBusinessTrans[field] + "\" value=\"" + userBusinessValues[field] + "\" class=\"form-control\"" +  requiredForm + "\" />\
                    </div>\
                </div>";
    }

    //bootstrap_3_layout
    return "<div class=\"form-group\">\
                <label for=\"" + field + "\" class=\"control-label" + requiredLabel + "\">" + userBusinessTrans[field] + "</label>\
                <input type=\"text\" id=\"" + field + "\" name=\"user_profile[" + field.replace("user_profile_", "") + "]\" placeholder=\"" + userBusinessTrans[field] + "\" value=\"" + userBusinessValues[field] + "\" class=\"form-control\"" +  requiredForm + "\" />\
            </div>";
}

//Displays specifics fields for Association/Business
function displayAssociationBusiness() {
    let fields = [
        "user_profile_businessName",
        "user_profile_businessAddress",
        "user_profile_businessAddress2",
        "user_profile_businessPostal",
        "user_profile_businessTown",
        "user_profile_businessCountry"
    ];

    fields.forEach(function(field) {
        $("#details")
            .append(displayField(field))
        ;
    });
}

//Displays specifics fields for Business
function displayBusiness() {
    let fields = [
        "user_profile_businessSiret",
        "user_profile_businessVat"
    ];

    fields.forEach(function(field) {
        $("#details")
            .append(displayField(field))
        ;
    });
}

$(document).ready(function ($) {
//Adds details div used to display user specific data
    $("#user_profile_businessType_0")
        .closest(".form-group")
        .after("<div id=\"details\"></div>")
    ;
//Adds specific fields for Individual
    $("#user_profile_businessType_0").click(function() {
        removeFields();
    });
//Adds specific fields for Association
    $("#user_profile_businessType_1").click(function() {
        removeFields();
        displayAssociationBusiness();
    });
//Adds specific fields for Business
    $("#user_profile_businessType_2").click(function() {
        removeFields();
        displayAssociationBusiness();
        displayBusiness();
    });
});