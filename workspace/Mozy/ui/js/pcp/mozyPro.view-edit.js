/**
 * View refactorized
 * Shows information of the Global settings
 * @parent mozyPro.view-edit.html
 * @param {APS} load
 */
require(["aps/load", "aps/ready!"],
        function (load) {
            "use strict";
            
            /**
             * Returns aps/Output with data from the parameters
             * @param {string} label
             * @param {string} value
             * @returns {Array}
             */
            var getOutput = function (label, value, visibility) {
                var output =  ["aps/Output", { label: label, value: value, visible: visibility }];
                return output;
            };
            
            // Contexts
            var model = aps.context.vars.globals;

            // First container - Globals Settings
            var fsGlobalSettings = ["aps/FieldSet", {title: _("Global Settings")},
                [
                    getOutput(_("Sales Model"), (model.orderType) ? _("Reseller sale model") : _("Direct sale model"), true)
                ]
            ];
            
            // Second container - API Credentials
            var fieldSet = ["aps/FieldSet", { title: _("API Credentials") },
                                [
                                    getOutput("Web Service Prefix", model.ws_prefix, true),
                                    getOutput("Web Service Suffix", model.ws_sufix, true),
                                    getOutput("API Key", model.api_key, true),
                                    getOutput("Root Partner ID", model.root_partner_id, true),
                                    getOutput("Root Role ID", model.root_role_id, true),
                                    getOutput("URL Admin Panel", model.mozypro_adminpanel_login, true),
                                    getOutput("URL User Portal", model.mozypro_user_portal_url, true),
                                    getOutput("API PBA", model.APIPBA, model.orderType),
                                    getOutput("PBA User", model.userPBA, model.orderType),
                                    ["aps/Output", {title: "PBA Password",value: "**********"}]
                                ]
                            ];
          
            // Page Container - Adding API credentials container
            var pageContainer = ["aps/PageContainer", [ fsGlobalSettings, fieldSet ]];
            
            // on Page display
            load(pageContainer).then(function () {
                // Edit button - Click
                aps.app.onSubmit = function () {
                    aps.apsc.gotoView("mozyPro.edit");
                };
            });
            
            
        });

