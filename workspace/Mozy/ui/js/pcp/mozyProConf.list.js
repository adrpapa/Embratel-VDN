/**
 * View refactorized
 * List the existing brands in a grid and let the user create new ones
 * @parent mozyProConf.list.html
 * @param {aps/load} load
 * @param {aps/ResourceStore} Store // Connects to mozyProConf resources
 * @param {dijit/registry} registry // Buttons click handler and message handler
 * @param {aps/Message} Message // Display message for removing status
 */
require(["aps/load", "aps/ResourceStore", "dijit/registry", "aps/Message", "aps/ready!"],
        function (load, Store, registry, Message) {
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

            // Store to the linked resource
            var brandingStore = new Store({
                target: "/aps/2/resources/" + aps.context.vars.globals.aps.id + "/mozyProConf/"
            });

            // First container - Grid with branding customize information
            var grid = ["aps/Grid", {
                    id: "brandingGrid",
                    selectionMode: "single",
                    columns: [
                        {name: _("Logo URL"), field: "logoUrl"},
                        {name: _("Brand Tab Name"), field: "branded_tab_name", type: "resourceName"}
                    ],
                    store: brandingStore,
                    apsResourceViewId: "mozyProConf.edit"
                }, [
                    ["aps/Toolbar",
                        [["aps/ToolbarButton", {
                                    id: "AddNewBranding",
                                    label: _("New"),
                                    iconClass: "sb-add-mail-list"
                                }
                            ],
                            ["aps/ToolbarButton", {
                                    id: "DeleteBranding",
                                    label: _("Delete"),
                                    iconClass: "sb-delete",
                                    requireSingleItem: true
                                }
                            ]]
                    ]
                ]];

            // Page Container - Adding grid with branding information
            var pageContainer = ["aps/PageContainer", {id: "page"}, [grid]];

            // on Page Display
            load(pageContainer).then(function () {
                // New ToolbarButton onClick
                registry.byId("AddNewBranding").on("click", function () {
                    aps.apsc.gotoView("mozyProConf.new");
                });
                // Delete ToolbarButton onClick
                registry.byId("DeleteBranding").on("click", function () {

                    var grid = registry.byId("brandingGrid");
                    // apsId of the selected item
                    var apsIdSelected;
                    for (var prop in grid.get("selection")) {
                        if (prop) {
                            apsIdSelected = prop;
                        }
                    }
                    // Remove Branding resource from store
                    brandingStore.remove(apsIdSelected)
                        .then(function () {
                            // Updates the grid without item removed - @console throws "can't find the element"
                            grid.refresh();
                            displayMessage(_("Brand settings removed"), "info");
                            // Stops Delete button from "please wait" state
                            registry.byId("DeleteBranding").cancel();
                        // Error in deleting the resource
                        }).otherwise(function (deleteBrandingError) {
                            console.log("Error in deleting the resource");
                            console.dir(deleteBrandingError);
                            displayMessage(_("The brand setting cannot be deleted"), "error");
                            aps.apsc.cancelProcessing();
                    });

                });
            });


        });

