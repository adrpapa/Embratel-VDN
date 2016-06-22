/**
 * View refactorized
 * Let the user to edit and existing group or create another one
 * @parent mozyProConf.edit.html
 * @param {aps/load} load
 * @param {aps/ResourceStore} Store // Prepare a new resource to be created or an existing one to be updated
 * @param {dojox/mvc/getStateful} getStateful // Refresh information of the model on change
 * @param {dojox/mvc/at} at // Handle the onChange event
 * @param {dojox/mvc/getPlainValue} getPlainValue // Cleans context model
 * @param {dijit/registry} registry // Handles page to validation
 * @param {aps/Message} Message // Display error messages
 * @param {dojo/text!./json/mozyProConf.json} json_mozyProConf // base model of mozyProConf resource for creating new resources
 */
require(["aps/load", "aps/ResourceStore", "dojox/mvc/getStateful", "dojox/mvc/at", "dojox/mvc/getPlainValue", "dijit/registry", "aps/Message", "dojo/text!./json/mozyProAccountGroup.json", "aps/ready!"],
        function (load, Store, getStateful, at, getPlainValue, registry, Message, json_mozyProAccountGroup ) {
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
            
            // Contexts (for existing and new resources)
            var model = aps.context.vars.group || JSON.parse(json_mozyProAccountGroup);
            model = getStateful(model);

            // Prepare the store depending of the resource existence
            var storeTarget = "/aps/2/resources/";
            if (!aps.context.vars.group) {
                storeTarget += aps.context.vars.mozyProAccount.aps.id + "/mozyProAccountGroup";
            }
            var store = new Store({ target: storeTarget });

            // First Container - User group
            var fsGroup = ["aps/FieldSet", { title: _('User Groups:') },
                                [
                                    ["aps/TextBox", { label: _('Name:'), value: at(model, 'name'), required: true}]
                                ]
                            ];


            // Page Container - Adding the Customization settings and API credentials
            var pageContainer = ["aps/PageContainer", { id: "page" }, [ fsGroup ]];
            
            // on Page Display
            load(pageContainer).then(function () {
                // Submit button - Click
                aps.app.onSubmit = function () {
                    var page = registry.byId("page");
                    // Check every required field to be no empty
                    if (page.validate()) {
                        if (model.name !== "Default" && model.name.length > 3) {
                            // Save new or existing data
                            store.put(getPlainValue(model)).then(function () {
                                aps.apsc.gotoView("mozyProAccount.groups.list");
                            // Error in creating or updating the resource
                            }).otherwise(function (confError) {
                                console.log("Error updating/creating the group resource");
                                console.dir(confError);
                                displayMessage(confError, "error");
                                aps.apsc.cancelProcessing();
                            });
                        // The groups name is not valid
                        } else {
                            displayMessage(_("not valid"), "error");
                            aps.apsc.cancelProcessing();
                        }
                    // There is some field empty
                    } else {
                        displayMessage(_("Required fields have to be filled"), "warning");
                        aps.apsc.cancelProcessing();
                    }
                };
                
                // Cancel button - Click
                aps.app.onCancel = function () {
                    aps.apsc.gotoView("mozyProAccount.groups.list");
                };
            });
            
        });

