/**
 * View refactorized
 * Let the user change information of the Global settings
 * @parent mozyPro.edit.html
 * @param {aps/load} load
 * @param {aps/ResourceStore} Store // Prepare the resource to be updated
 * @param {aps/xhr} xhr // Call for checking the existence of subscriptions
 * @param {dojox/mvc/getStateful} getStateful // Refresh information of the model on change
 * @param {dojox/mvc/at} at // Handle the onChange event
 * @param {dojox/mvc/getPlainValue} getPlainValue // Cleans context model
 * @param {dijit/registry} registry // Get page
 * @param {aps/Message} Message // Display error messages
 */
require(["aps/load", "aps/ResourceStore", "aps/xhr", "dojox/mvc/getStateful", "dojox/mvc/at", "dojox/mvc/getPlainValue", "dijit/registry", "aps/Message", "aps/ready!"],
        function (load, Store, xhr, getStateful, at, getPlainValue, registry, Message) {
            "use strict";
            
            /**
             * Display messages in the page
             * @param {string} message
             * @param {string} msgType
             * @returns {void}
             */
            var displayMessage = function (message, msgType) {
                var getMessageList = registry.byId("page").get("messageList");
                getMessageList.removeAll();
                getMessageList.addChild(new Message({description: message, type: msgType}));
            };
            
            /**
             * Returns aps/TextBox with data from the parameters
             * @param {string} labelString
             * @param {string} valueString
             * @returns {Array}
             */
            var getTextBox = function (labelString, idString, valueString, required) {
                var parameters = { id: idString, label: labelString, value: valueString };
                // Add required property if needed
                if (required) parameters.required = true;
                var textbox =  ["aps/TextBox", parameters ];
                return textbox;
            };

            /**
             * Change the requirement of some fields depending of the sale model clicked
             * @param {boolean} orderType {false: direct model, true: reseller model}
             * @param {object} model
             * @returns {object} model
             */
            var optionSaleModel = function (orderType, model) {
                var radioDirect = registry.byId("directmodel");
                var radioReseller = registry.byId("resellermodel");
                // Fix radiobutton click event malfunction
                if (!orderType && radioDirect.get("checked") === false) {
                    radioDirect.set("checked", true);
                    radioReseller.set("checked", false);
                } else
                if (orderType && radioReseller.get("checked") === false) {
                    radioReseller.set("checked", true);
                    radioDirect.set("checked", false);
                }
                // Display the fields required for each sale model
                model.orderType = orderType;
                registry.byId("api_key").set("required", !orderType);
                registry.byId("root_partner_id").set("required", !orderType);
                registry.byId("root_role_id").set("required", !orderType);
                registry.byId("api_pba").set({"required": orderType, "visible": orderType});
                return model;
            };
            
            // Contexts
            var model = getStateful(aps.context.vars.globals);
            
            // First container - API Credentials
            var fsGlobalsSettings = ["aps/FieldSet", { title: _("Global Settings") },
                                [
                                    ["aps/RadioButton", {
                                        id: "directmodel",
                                        label: _("Sales Model"),
                                        name: "salesModel",
                                        checked: !model.orderType,
                                        description: _("Direct sale model"),
                                        hint: _("Customers buy package from Service Provider"),
                                        onClick: function () { model = optionSaleModel(false, model); }
                                    }],
                                    ["aps/RadioButton", {
                                        id: "resellermodel",
                                        name: "salesModel",
                                        checked: model.orderType,
                                        description: _("Reseller sale model"),
                                        hint: _("Resellers buy package from Service Provider"),
                                        onClick: function () { model = optionSaleModel(true, model); }
                                    }]
                                ]
                            ];
                            
            // Second container - API Credentials
            var fieldSet = ["aps/FieldSet", { title: _("API Credentials") },
                                [
                                    getTextBox("Web Service Prefix", "ws_prefix", at(model, "ws_prefix"), true),
                                    getTextBox("Web Service Suffix", "ws_sufix", at(model, "ws_sufix"), true),
                                    getTextBox("API Key", "api_key", at(model, "api_key"), !model.orderType),
                                    getTextBox("Root Partner ID", "root_partner_id", at(model, "root_partner_id"), !model.orderType),
                                    getTextBox("Root Role ID", "root_role_id", at(model, "root_role_id"), !model.orderType),
                                    getTextBox("URL Admin Panel", "url_admin", at(model, "mozypro_adminpanel_login"), true),
                                    getTextBox("URL User Portal", "url_user", at(model, "mozypro_user_portal_url"), true),
                                    getTextBox("API PBA", "APIPBA", at(model, "APIPBA"), model.orderType),
                                    ["aps/Output", { value: _("If the endpoint requires HTTP validation, please enter a Username and Password.") }],
                                    getTextBox("PBA User", "userPBA", at(model, "userPBA"), false),
                                    ["aps/Password", {
                                        id: "PBA Password",
                                        title: "PBA Password",
                                        value: at(model, "passPBA"),
                                        showResetButton: true,
                                        showStrengthIndicator : true,
                                        autoSize: true,
                                        required: false
                                    }]
                                ]
                            ];
            
            // Page Container - Adding API credentials container
            var pageContainer = ["aps/PageContainer", { id: "page" }, [ fsGlobalsSettings, fieldSet ]];
            
            xhr("/aps/2/resources/" + model.aps.id + "/mozyProAccount").then(function (subscriptions) {
                var subsNumber = subscriptions.length || 0;
                // on Page Display
                load(pageContainer).then(function () {
                    if (subsNumber > 0) {
                        registry.byId("directmodel").set("disabled", true);
                        registry.byId("resellermodel").set("disabled", true);
                    }
                    // Submit button - Click
                    aps.app.onSubmit = function () {
                        var page = registry.byId("page");
                        // Check every required field to be no empty
                        if (page.validate()) {
                            // Store for the resource
                            var store = new Store({
                                target: "/aps/2/resources/"
                            });

                            // PUT to upload new data
                            store.put(getPlainValue(model)).then(function () {
                                aps.apsc.gotoView("mozyPro.view-edit");

                                // Error in update globals resource
                            }).otherwise(function (storePutError) {
                                console.log("Error updating globals resource");
                                console.dir(storePutError);
                                if(storePutError.status === 411)
                                {
                                    displayMessage(_("PBA Credentials are not correct"), "error");
                                    aps.apsc.cancelProcessing();
                                }
                                else
                                {
                                    displayMessage(_("Cannot update Globals settings"), "error");
                                    aps.apsc.cancelProcessing();
                                }
                            });
                            // There is some field empty
                        } else {
                            displayMessage(_("Required fields have to be filled"), "warning");
                            aps.apsc.cancelProcessing();
                        }
                    };
                    // Cancel button - Click
                    aps.app.onCancel = function () {
                        aps.apsc.gotoView("mozyPro.view-edit");
                    };
                });
            // Error in comunication with server
            }).otherwise(function (errorRetrievingSubscription) {
                displayMessage("Connection with the server failed", "error");
                console.dir(errorRetrievingSubscription);
            });
            
        });
