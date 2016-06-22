/**
 * View refactorized
 * Manage the user creation and edition, the license assignment and edition, the group change and the sync enable or disable
 * @parent mozyProAccountUser.list.html
 * @param {aps/load} load
 */
require(["aps/load",
        "dojo/promise/all",
        "dojo/Deferred",
        "aps/ResourceStore",
        "aps/Memory",
        "aps/xhr",
        "dijit/registry",
        "aps/Message",
        "aps/WizardData",
        "dojox/mvc/at",
        "dojox/mvc/StatefulArray",
        "aps/Container",
        "aps/Grid",
        "aps/FieldSet",
        "aps/Button",
        "aps/RadioButton",
        "aps/Slider",
        "aps/Output",
        "aps/TextBox",
        "aps/Password",
        "dojo/query",
        "dojo/text!./json/mozyProAccountUserLicense.json",
        "dojo/text!./json/mozyProAccount.newUser.suwizard.json",
        "dojo/text!./json/mozyProAccount.newServiceUser.json",
        "dojox/mvc/getStateful",
        "dojox/mvc/getPlainValue",
        "aps/ready!"],
        function (load, all, Deferred, Store, Memory, xhr, registry, Message, WizardData, at, StatefulArray, Container, Grid, Fieldset, Button, RadioButton,Slider, Output, Textbox, Password, Query, json_UserLicense, json_NewUser, json_NewServiceUser, getStateful, getPlainValue) {
            "use strict";

            // Process Status methods
            var workingProcess = false;
            var initProcess = function () { workingProcess = true; aps.apsc.showLoading(); };
            var endProcess = function () { workingProcess = false; aps.apsc.hideLoading(); };
            var busyWarning = function () { displayMessage(_("You must wait until the current operation is finished."), "warning"); };
            // __ End of Process Status methods
        
    // Message Display methods
            /**
             * Display messages in the page
             * @param {string} message
             * @param {string} msgType
             */
            var displayMessage = function (message, msgType) {
                var getMessageList = registry.byId("page").get("messageList");
                getMessageList.removeAll();
                getMessageList.addChild(new Message({description: message, type: msgType}));
            };
            
            /**
             * Display error messages in the page and console
             * @param {object} error 
             * @param {string} message
             * @param {string} cancelButtonIdProcess (optional)
             */
            var displayErrorMessage = function (error, message, cancelButtonIdProcess) {
                var errorDisplay = error;
                if (typeof error === 'object') {
                    try {
                        errorDisplay = "Error (" + error.status + ") - " + JSON.parse(error.response.text).message;
                    } catch (exception) {
                        console.log("Error not catched properly. Exception ", exception);
                    }
                }
                console.log(errorDisplay);
                // Displays the string message to the user
                displayMessage(message, "error");
                // Cancel the button waiting state (if needed)
                if (cancelButtonIdProcess) registry.byId(cancelButtonIdProcess).cancel();
                endProcess();
            };
            
            /**
             * Retrieve messages sended from another view
             */
            var checkForMessages = function () {
                var cacheMsg = WizardData.get();
                if (cacheMsg) {
                    try { displayMessage(cacheMsg.message, cacheMsg.type); }
                    catch (e) { console.log(cacheMsg); }
                }
                WizardData.put("");
            };
    // __ End of Message Display method
            
            /**
             * Updates slider parameters with variable information from the account licenses
             * @param {string} userApsId
             * @param {string} type
             * @param {boolean} defaultValue (optional)
             */
            var updateSlider = function (userApsId, type, defaultValue) {
                var maximumQuota, radioButtonSwitch;
                var sync = (type === "sync") ? true : false;
                // Selects the maximum parameter and prepares a switch to check radiobutton depending on the license type
                if (type === "Desktop" || sync) {
                    maximumQuota = mozyAccount.desktopQuota.limit - ((mozyAccount.desktopQuota.usage) ? mozyAccount.desktopQuota.usage: 0);
                    radioButtonSwitch = true;
                } else {
                    maximumQuota = mozyAccount.serverQuota.limit - ((mozyAccount.serverQuota.usage) ? mozyAccount.serverQuota.usage: 0);
                    radioButtonSwitch = false;
                }
                // Adds the defaultValue to the maximum value of the slider
                if (defaultValue && !sync) maximumQuota += parseInt(defaultValue, 10);
                // Updates the slider
                var qSliderDesktop = registry.byId("qsLicense_" + userApsId);
                    qSliderDesktop.set({ "maximum": maximumQuota, "minimum": 0, "legend": _('Max.: __maximumQuota__ GB', {"maximumQuota": maximumQuota}), value: (defaultValue ? defaultValue : 0)  });
                // Depending of a optional parameter, check radiobutton of the license type
                if (!defaultValue && !sync) {
                    registry.byId("rbDesktop_" + userApsId).set("checked", radioButtonSwitch);
                    registry.byId("rbServer_" + userApsId).set("checked", !radioButtonSwitch);
                }
            };
            
            /**
             * Create dojo widgets to modify license data with a slider and, optionally, to radiobuttons for each type of license
             * @param {string} userApsId
             */
            var _createLicenseBox = function (userApsId) {
                // If the Box is already showed, destroy it for avoid confusing ids
                if (registry.byId("cntViewLicense_" + userApsId)) registry.byId("cntViewLicense_" + userApsId).destroyRecursive();
                // Creates a slider with basic information and Id
                var quotaSlider = new Slider({title: _("Quota"), label: _("Quota"), id: "qsLicense_" + userApsId, minimum: 0, maximum: 0});
                
                var fsetViewLicense = new Fieldset({id: "fsviewLicense_" + userApsId});

                var containerTitle = _('New License');
                var rbDesktop = new RadioButton({label: _("Type"), id: "rbDesktop_" + userApsId, name: "licType_" + userApsId, description: _("Desktop") });
                var rbServer = new RadioButton({id: "rbServer_" + userApsId, name: "licType_" + userApsId, description: _("Server") });
                // Include the radiobuttons in the fieldset
                fsetViewLicense.addChild(rbDesktop);
                fsetViewLicense.addChild(rbServer);

                // Add the slider to the fieldset
                fsetViewLicense.addChild(quotaSlider);

                // Include buttons in its own container
                var contButtons = new Container({});

                // Prepare the container for the whole license box
                var contViewLicense = new Container({id: "cntViewLicense_" + userApsId, title: containerTitle});
                contViewLicense.addChild(fsetViewLicense);
                contViewLicense.addChild(contButtons);
                // Depending of the mode add the container to the activeItem or newuser container
                registry.byId("newUserContainer").addChild(contViewLicense);
            };
            
            /**
             * Check for licenses available
             * @param {string} type Desktop/Server
             * @returns {Boolean}
             */
            var unusedLicenses = function (type) {
                var account = aps.context.vars.mozyProAccount;
                var enoughLicenses = true;
                var max, current;
                if (type === "Desktop") {
                    max = account.desktopLicenseNum.limit;
                    current = account.desktopLicenseNum.usage || 0;

                } else if (type === "Server") {
                    max = account.serverLicenseNum.limit;
                    current = account.serverLicenseNum.usage || 0;
                }
                if (max - current === 0) {
                    enoughLicenses = false;
                }
                
                return enoughLicenses;
            };
    
    // __ End of License Management method
            
    // User Management methods            
            /**
             * Create a container with widgets for creating a user (and a grid with unassigned users if procced)
             * @returns {object} newServiceUserModel
             */
            var _createUserBox = function () {
                // Prepares a empty service user model
                var newServiceUserModel = JSON.parse(json_NewServiceUser);

                // Container with the fieldset
                //var contUser = new Container({title: _('New User'), id: "newUserContainer"});
                var contUser = new Container({id: "newUserContainer"});
                //contUser.addChild(fsetNewUser);
                // Hides the list switcher
                registry.byId("listSwitcher").set("visible", false);
                // if there is an object with the grid, add it to the parent container
                //if (gridUnassignedUsers) registry.byId("newUserGeneralContainer").addChild(gridUnassignedUsers);
                // Add the container to the parent container
                registry.byId("newUserGeneralContainer").addChild(contUser);
                // Add a license box 
                _createLicenseBox("new", "newuser");
                // Returns the service user model
                return newServiceUserModel;
            };
            
            /**
             * Checks context account var and calculates available licenses of the type passed as parameter
             * @param {string} type
             * @returns {Number}
             */
            var _getAvailableLicenses = function (type) {
                var account = aps.context.vars.mozyProAccount;
                var licenseNum = (type === "Desktop") ? account.desktopLicenseNum: account.serverLicenseNum;
                var availableLicenses = licenseNum.limit - ((licenseNum.usage) ? licenseNum.usage: 0);
                return availableLicenses;
            };
            
            /**
             * Refresh grid depending on available licenses
             */
            var refreshGridCheckBoxes = function (licenseType) {
                var blockTheGrid = (new Query("input[type=checkbox]:checked").length < _getAvailableLicenses(licenseType)) ? false : true;
                new Query("input[type=checkbox]").forEach( function (node) {
                    if (node.checked === false) node.disabled = blockTheGrid;
                });
            };
            
            /**
             * Displays the new user box with a grid for existing users if there is at least one
             * @param {array} userList
             */
            var showUserBox = function () {
                
                // Check if there are at least one license available of any type
                if (unusedLicenses("Desktop") || unusedLicenses("Server")) {
                    // Prepares a model with the model of the new user (if it is only one)
                    var suModel;
                    suModel = _createUserBox();

                    // Add functionality to the radiobuttons to update the slider if there are licenses available of the type
                    if (unusedLicenses("Desktop"))
                    {
                        // radbiobutton - Click
                        registry.byId("rbDesktop_new").on("click", function () {
                            if (!registry.byId("rbDesktop_new").get("disabled"))
                            {
                                refreshGridCheckBoxes("Desktop");
                                updateSlider("new", "Desktop");
                            }
                        });
                    // Disable the radiobutton it there are no licenses available
                    }
                    else
                    {
                        registry.byId("rbDesktop_new").set({disabled: true});
                    }
                    
                    if (unusedLicenses("Server"))
                    {
                        // radbiobutton - Click
                        registry.byId("rbServer_new").on("click", function () {
                            if (!registry.byId("rbServer_new").get("disabled")) {
                                refreshGridCheckBoxes("Server");
                                updateSlider("new", "Server");
                            }
                        });
                    }
                    else // Disable the radiobutton it there are no licenses available
                    {
                        registry.byId("rbServer_new").set({disabled: true});
                    }

                    aps.app.onNext = function() {
                        assignUserList(); //}
                    };
                // No licenses available
                } else {
                    registry.byId("newUserButton").cancel();
                    displayMessage(_("There are not available licenses"), "warning");
                }
            };
            
            /**
             * Assign a user and license for each service user selected
             * @param {array} arrayServiceUsersApsId
             */
            var assignUserList = function () {

                if (!workingProcess)
                {
                    initProcess();
                    
                    // Get the quota that will be assigned to each user
                    var quotaPerUser = registry.byId("qsLicense_new").value;
                    
                    // Get the maximum quota available
                    var maxQuota = registry.byId("qsLicense_new").maximum;
                    
                    // Check if there are enough quota for all the users
                    //if ((arrayServiceUsersApsId.length * quotaPerUser) <= maxQuota && quotaPerUser > 0)
                    if (quotaPerUser <= maxQuota && quotaPerUser > 0)
                    {
                        assignBulkUser();
                    // Error setting the quota
                    }
                    else
                    {
                        aps.apsc.cancelProcessing();
                        endProcess();
                        displayErrorMessage('', _("Error: 'Quota' value is out of range"), "btnCreateLicense_new");
                        
                    }
                // A process is still working
                }
                else
                {
                    busyWarning();
                    //registry.byId("btnCreateLicense_new").cancel();
                }
            };
            
            /**
             * Launch asynchronous requests for creating user
             * @returns {Deferred.promise}
             */
            var assignBulkUser = function () {

                //Service User
                var user = getStateful(aps.context.params.user);

                // Prepares the deferred
                var deferred = new Deferred();
                
                var defaultGroupId = aps.context.vars.mozyProAccount.user_group_id;
                // Prepare a new user empty model 
                var newUserModel = JSON.parse(json_NewUser);
                // Associate information from the service user and default group
                newUserModel.displayName = (typeof user.displayName != 'undefined' ? user.displayName : '');
                newUserModel.login = (typeof user.login != 'undefined' ? user.login : '');
                newUserModel.userId = (typeof user.userId != 'undefined' ? user.userId : '');
                newUserModel.user_group_id = defaultGroupId;
                newUserModel.user_group_id_OLD = defaultGroupId;
                newUserModel.user.aps.id = '';
                newUserModel.mozyProAccount.aps.id = aps.context.vars.mozyProAccount.aps.id;
                
                if (typeof user.aps != 'undefined'){
                    newUserModel.user.aps.id = (typeof user.aps.id != 'undefined' ? user.aps.id : '');
                }
                
                if(registry.byId("rbDesktop_new").get("checked"))
                {
                    newUserModel.desktopQuotaSum = registry.byId("qsLicense_new").value;
                    newUserModel.desktopLicSum = 1;
                    newUserModel.swTypeLicense = 1;
                }
                else
                {
                    newUserModel.serverQuotaSum = registry.byId("qsLicense_new").value;
                    newUserModel.serverLicSum = 1;
                    newUserModel.swTypeLicense = 2;
                }

                /* For POA 6.x, use the aps.apsc.next method for navigation */
                aps.apsc.next({ objects: [getPlainValue(newUserModel)], userAttr: "user"});
                /* For POA 5.5, replace the above call with aps.apsc.gotoView as follows */
                //aps.apsc.gotoView("empty", null, { objects: [getPlainValue(model)], userAttr: "user" });
                
                return deferred.promise;
            };

    // __ End of User Management methods

    // Page Widgets
            // Contexts
            var mozyAccount = aps.context.vars.mozyProAccount;

            // Store to Mozy Users
            var usersStore = new Store({
                target: "/aps/2/resources/" + mozyAccount.aps.id + "/mozyProAccountUser"
            });
            
            // First Container - Summary
            var newUserContainer = ["aps/Container", {id: "newUserGeneralContainer"},
                [
                    //["aps/Output", {innerHTML: _("Here you can create new users, list your users and assign new licenses to the users.<br><br/>")}]
                ]
            ];

            // Request information about users and groups
            all({
                userList: usersStore.query(),
                groupList: xhr("/aps/2/resources/" + mozyAccount.aps.id + "/mozyProAccountGroup")
            }).then(function(response){
                // Save responses
                var userList = response.userList;
                var groupList = response.groupList;
                var arrLicenses = [];
                for (var i = 0; i < userList.length; i++) {
                    arrLicenses.push(xhr("/aps/2/resources/" + userList[i].aps.id + "/mozyProAccountUserLicense"));
                }

                all(arrLicenses).then(function () {
                    // Prepare Active Items array
                    var activeItems = [];
                    //activeItems.push(["aps/Button", {id: "newUserButton", title: _("Create new User"), onClick: function () { showUserBox(userList);} }]);
                    
                    // For each user
                    for (var i = 0; i < userList.length; i++) {
                        // model declaration with users received from Store
                        var model = userList[i];
                        // Obtain the groups
                        var userGroup; // Used only for displaying group name in the Active Item
                        var userGroupsOptions = []; // Used for changing groups
                        for (var j = 0; j < groupList.length; j++) {
                            var userGroupName = (groupList[j].name === "Default") ? _("Default") : groupList[j].name;
                            if (String(model.user_group_id) === String(groupList[j].groupId)) {
                                userGroup = groupList[j].name;
                                userGroupsOptions.push({label: userGroupName, value: groupList[j].groupId, selected: true});
                            } else {
                                userGroupsOptions.push({label: userGroupName, value: groupList[j].groupId});
                            }
                        }
                    }

                    // List Switcher - Grid view

                    // List Switcher 
                    var listSwitcher = ["aps/ListSwitcher", { id: "listSwitcher" },
                                        [
                                            ["aps/ActiveList", activeItems]
                                        ]];

                    // Check if partner canceled his subscription
                    var subscriptionStatus = mozyAccount.partnerStatus || "enabled";
                    var arrayContent = [newUserContainer, listSwitcher];
                    // Page Container
                    var pageContainer = ["aps/PageContainer", {id: "page"}, (subscriptionStatus === "disabled") ? [] : arrayContent];

                    // on Page Load
                    load(pageContainer).then(function () {
                        // Load a message left it before
                        checkForMessages();
                        // Show a message if the partner has disabled the subscription
                        if (subscriptionStatus === "disabled") displayMessage(_("The partner has disabled the subscription"), "error");
                    });

                    showUserBox(userList);
                });
            });

        });