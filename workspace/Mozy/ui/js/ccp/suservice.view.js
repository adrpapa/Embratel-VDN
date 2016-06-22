/**
 * View refactorized
 * Manage the user creation and edition, the license assignment and edition, the group change and the sync enable or disable
 * @parent mozyProAccountUser.list.html
 * @param {aps/load} load
 */
require(["aps/load", "dojo/promise/all", "dojo/Deferred", "aps/ResourceStore", "aps/Memory", "aps/xhr", "dijit/registry", "aps/Message", "aps/WizardData",
            "aps/Container", "aps/FieldSet", "aps/Button", "aps/RadioButton", "aps/Slider", "aps/Output",
            "dojo/text!./json/mozyProAccountUserLicense.json", "aps/ready!"],
        function (load, all, Deferred, Store, Memory, xhr, registry, Message, WizardData,
                    Container, Fieldset, Button, RadioButton, Slider, Output,
                    json_UserLicense) {
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
            
            /**
             * Left a message for next navigation view
             * @param {string} message
             * @param {string} type
             */
            var setCacheMessage = function (message, type) {
                WizardData.put({message: message, type: type});
            };
            
    // __ End of Message Display method
        
    // Interface methods
            /**
             * It returns the time elapsed between the date passed by a parameter and the current date
             * @param {string} date
             * @returns {object}
             */
            var timeTilNow = function (date) {
                // Date Object with the last update
                var lastDate = new Date(date);
                // Date object with the actual time
                var now = new Date();
                // Difference between now and last update in seconds
                var diff = (now.getTime() - lastDate.getTime()) / 1000;
                // Steps to compare with the difference. In seconds: 1 minute, 1 hour, 1 day, 1 month, 1 year
                var arrMagnitude = [60, 60 * 60, 60 * 60 * 24, 60 * 60 * 24 * 30, 60 * 60 * 24 * 30 * 12];
                // Compare the difference with each order of magnitude
                var response;
                if (diff < arrMagnitude[0]) {
                    response = {diff: Math.floor(diff), message: _("${value} second(s) ago")}; } else
                if (diff < arrMagnitude[1]) {
                    response = {diff: Math.floor(diff / arrMagnitude[0]), message: _("${value} minute(s) ago")}; } else
                if (diff < arrMagnitude[2]) {
                    response = {diff: Math.floor(diff / arrMagnitude[1]), message: _("${value} hour(s) ago")}; } else
                if (diff < arrMagnitude[3]) {
                    response = {diff: Math.floor(diff / arrMagnitude[2]), message: _("${value} day(s) ago")}; } else
                if (diff < arrMagnitude[4]) {
                    response = {diff: Math.floor(diff / arrMagnitude[3]), message: _("${value} month(s) ago")}; }
                else {
                    response = {diff: 1, message: _("more than a year ago")};
                }
                // Return formated the time until now and the message to show
                return response;
            };
            
            /**
             * Controls the interface display depending on the account type and the group
             * @param {string} userGroup
             * @returns {Boolean}
             */
            var expandedInterface = function (userGroupId) {
                var xpInterface = true;
                if (aps.context.vars.mozyProAccount.accountType === "C") {
                    if (String(userGroupId) !== String(aps.context.vars.mozyProAccount.user_group_id)) xpInterface = false;
                }
                return xpInterface;
            };
    // __ End of interface methods
        
    // Group Change method
            /**
             * Change the group of a user 
             * @param {string} userApsId
             * @param {array} userGroupsOptions
             */
            var changeUserGroup = function (userApsId, userGroupsOptions) {
                if (!workingProcess) {
                    initProcess();
                    // Object to save new group data 
                    var newGroup = {id: registry.byId("selUserGroup_" + userApsId).get("value")};
                    // Search for the new group data name
                    for (var i = 0; i < userGroupsOptions.length; i++) {
                        if (newGroup.id === String(userGroupsOptions[i].value)) {
                            newGroup.name = userGroupsOptions[i].label;
                        }
                    }
                    // Declares Store for getting Group Resource
                    var userStore = new Store({
                        target: "/aps/2/resources/",
                        type: "http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1"
                    });
                    // Request to the server
                    userStore.get(userApsId).then(function (userModel) {
                        // If the new group is the same than the existing group, exit
                        if (userModel.user_group_id !== newGroup.id) {
                            // Change the model with the new group id
                            userModel.user_group_id_OLD = userModel.user_group_id;
                            userModel.user_group_id = newGroup.id;
                            // Request to the server to update the group
                            userStore.put(userModel).then(function () {
                                // Refresh the page
                                refreshPage(_("Group updated."), "info");
                            // Error updating the group
                            }).otherwise(function (errorGroupUpdate) {
                                // The user cannot change group because it has noactivated licenses
                                if (errorGroupUpdate.status === 410) {
                                    displayMessage(_('The user group could not be changed because the user has one no active license.'), "error");
                                    registry.byId("btnUpdateUserGroup_" + userApsId).cancel();
                                    endProcess();
                                } else {
                                    displayErrorMessage(errorGroupUpdate, _('The user group could not be changed.'), "btnUpdateUserGroup_" + userApsId);
                                }
                            });
                        // The user group is the same than the existing one
                        } else {
                            registry.byId("btnUpdateUserGroup_" + userApsId).cancel();
                            endProcess();
                        }
                        // Error retrieving the group
                    }).otherwise(function (errorGroupGet) {
                        displayErrorMessage(errorGroupGet, _("There has been a problem with changing group."), "btnUpdateUserGroup_" + userApsId);
                    });
                } else {
                    busyWarning();
                }
            };
    // __ End of Group Change method
            
    // License Management methods
            /**
             * Intermediate function
             * Remove the licenses of a user previous to remove the user
             * @parent removeUser
             * @param {string} userApsId
             * @returns {Deferred.promise}
             */
            var _licenseRemover = function (userApsId) {
                // Prepare the deferred object
                var deferred = new Deferred();
                // Prepare the Store for the user licenses
                var licensesStore = new Store({
                    target: "/aps/2/resources/" + userApsId + "/mozyProAccountUserLicense"
                });
                // Request to the server 
                licensesStore.query().then(function (licenses) {
                    // Check that the user has licenses
                    if (licenses === undefined || licenses.length === 0) {
                        deferred.resolve();
                    } else {
                        // Prepare the Store to delete the licenses
                        var deleteLicensesStore = new Store({ target: "/aps/2/resources/" });
                        // Array for sending all request asynchronously
                        var arrRemoveLicenses = [];
                        for (var i = 0; i < licenses.length; i++) {
                            arrRemoveLicenses.push(deleteLicensesStore.remove(licenses[i].aps.id));
                        }
                        // Wait for all the orders to end
                        all(arrRemoveLicenses).then(function (removerResponse) {
                            // Check every license was removed
                            for (var i = 0; i < removerResponse.length; i++) {
                                if (removerResponse[i] !== "") deferred.cancel({ deleteResponse:removerResponse[i] });
                            }
                            // Resolve the deferred if it was not canceled
                            if (!deferred.isCanceled()) deferred.resolve();
                        // Error in removing one or more licenses
                        }).otherwise(function (licenseDeleteError) {
                            console.log("There is an error in deleting mozyProAccountUserLicense");
                            deferred.cancel(licenseDeleteError);
                        });
                    }
                // Error retrieving user licenses
                }).otherwise(function(licenseStoreError) {
                    console.log("There is an error in query to mozyProAccountUserLicense");
                    deferred.cancel(licenseStoreError);
                });
                // Return the deferred promise
                return deferred.promise;
            };
            
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
             * Updates the license quota from an existing one
             * @param {string} userApsId
             * @param {object} licenseModel // Model of the existing license
             */
            var updateLicense = function (userApsId, licenseModel) {
                if (!workingProcess) {
                    initProcess();
                    // Creates a model with needed information to update
                    var licenseUpdateModel = {};
                    licenseUpdateModel.aps = licenseModel.aps;
                    licenseUpdateModel.quota = registry.byId("qsLicense_" + userApsId).value;
                    // Prepare Store for license update
                    var storeLicense = new Store({ target: "/aps/2/resources/" });
                    // Request to server
                    storeLicense.put(licenseUpdateModel).then(function (){
                        // Refresh the page
                        refreshPage(_("The license has been updated successfully."), "info");
                    // Error updating the license
                    }).otherwise(function (errorUpdatingLicense) {
                        displayErrorMessage(errorUpdatingLicense, _("The license has not been updated successfully."), "btnUpdateLicense_" + userApsId);
                    });
                } else {
                    busyWarning();
                };
            };
            
            /**
             * Create dojo widgets to modify license data with a slider and, optionally, to radiobuttons for each type of license
             * @param {string} userApsId
             * @param {string} mode // update / create / newuser
             */
            var _createLicenseBox = function (userApsId, mode) {
                // If the Box is already showed, destroy it for avoid confusing ids
                if (registry.byId("cntViewLicense_" + userApsId)) registry.byId("cntViewLicense_" + userApsId).destroyRecursive();
                // Creates a slider with basic information and Id
                var quotaSlider = new Slider({title: _("Quota"), label: _("Quota"), id: "qsLicense_" + userApsId, minimum: 0, maximum: 0});
                
                var fsetViewLicense = new Fieldset({id: "fsviewLicense_" + userApsId});
                // Prepares variables for dynamic container title and button id and label
                var btnSubmit, containerTitle;
                // Depending of the mode param change the radiobuttons and the button labels
                if (mode === "update") {
                    btnSubmit = new Button({id: "btnUpdateLicense_" + userApsId, label: _('Edit License')});
                    containerTitle = _('Edit License');
                } else if (mode === "sync") {
                    btnSubmit = new Button({id: "btnUpdateSync_" + userApsId, label: _("Edit Sync Quota")});
                    containerTitle = _("Edit Sync Quota");
                } else {
                    // If the mode is create only change the button label
                    var buttonLabel = (mode === "create") ? _("Create License") : _("Add User and License");
                    btnSubmit = new Button({id: "btnCreateLicense_" + userApsId, label: buttonLabel});
                    containerTitle = _('New License');
                    var rbDesktop = new RadioButton({label: _("Type"), id: "rbDesktop_" + userApsId, name: "licType_" + userApsId, description: _("Desktop") });
                    var rbServer = new RadioButton({id: "rbServer_" + userApsId, name: "licType_" + userApsId, description: _("Server") });
                    // Include the radiobuttons in the fieldset
                    fsetViewLicense.addChild(rbDesktop);
                    fsetViewLicense.addChild(rbServer);
                }
                // Add the slider to the fieldset
                fsetViewLicense.addChild(quotaSlider);
                // Cancel button is always the same
                var btnCancelLicense = new Button({id: "btnCancelLicense_" + userApsId, label: _("Cancel")});
                // Include buttons in its own container
                var contButtons = new Container({});
                contButtons.addChild(btnSubmit);
                contButtons.addChild(btnCancelLicense);
                // Prepare the container for the whole license box
                var contViewLicense = new Container({id: "cntViewLicense_" + userApsId, title: containerTitle});
                contViewLicense.addChild(fsetViewLicense);
                contViewLicense.addChild(contButtons);
                // Depending of the mode add the container to the activeItem or newuser container
                if (mode === "newuser") {
                    registry.byId("newUserContainer").addChild(contViewLicense);
                } else {
                    registry.byId("activeItem_" + userApsId).addChild(contViewLicense);
                }
            };

            /**
             * Displays the license box for updating the quota. No Radiobuttons in it
             * @param {object} licenseModel
             * @param {string} userApsId
             */
            var showUpdateLicenseBox = function (licenseModel, userApsId) {
                // Request a base license box in mode "update"
                _createLicenseBox(userApsId, "update");
                // Updates the slider with the license information
                updateSlider(userApsId,licenseModel.licenseType, licenseModel.quota);
                // Adds functionality to the buttons for updating the license or cancel the process
                registry.byId("btnUpdateLicense_" + userApsId).on("click", function () { updateLicense(userApsId, licenseModel); });
                registry.byId("btnCancelLicense_" + userApsId).on("click", function () { hideLicenseBox(userApsId); });
            };
            
            /**
             * Displays the sync box for updating the sync quota.
             * @param {string} userApsId
             * @param {string} defaultQuota
             */
            var showUpdateSyncBox = function (userApsId, defaultQuota) {
                // Request a base license box in mode "sync"
                _createLicenseBox(userApsId, "sync");
                // Updates the slider with the license information. Sync quota requires desktop quota
                updateSlider(userApsId, "sync", defaultQuota);
                // Adds functionality to the buttons for updating the sync quota or cancel the process
                registry.byId("btnUpdateSync_" + userApsId).on("click", function () { editSyncQuota(userApsId); });
                registry.byId("btnCancelLicense_" + userApsId).on("click", function () { hideLicenseBox(userApsId); });
            };
            
            /**
             * Displays the license box for adding a license to a user.
             * @param {string} userApsId
             */
            var showLicenseBox = function (userApsId) {
                // Check if there are at least one license available of any type
                if (unusedLicenses("Desktop") || unusedLicenses("Server")) {
                    // Request a base license box in mode "create" (include radiobuttons)
                    _createLicenseBox(userApsId, "create");
                    // Adds functionality to the radiobuttons for refreshing the slider if there are licenses available of the type
                    if (unusedLicenses("Desktop")) {
                        registry.byId("rbDesktop_" + userApsId).on("click", function () { updateSlider(userApsId, "Desktop"); });
                    } else {
                        registry.byId("rbDesktop_" + userApsId).set({disabled: true});
                    }
                    if (unusedLicenses("Server")) {
                        registry.byId("rbServer_" + userApsId).on("click", function () { updateSlider(userApsId, "Server"); });
                    } else {
                        registry.byId("rbServer_" + userApsId).set({disabled: true});
                    }
                    // Adds functionality to the buttons for creating the license or cancel the process
                    registry.byId("btnCreateLicense_" + userApsId).on("click", function () { createLicense(userApsId); });
                    registry.byId("btnCancelLicense_" + userApsId).on("click", function () { hideLicenseBox(userApsId); });
                    // Expand the widget to see the license box
                    registry.byId("activeItem_" + userApsId).set("collapsed", false);
                // No licenses available
                } else {
                    registry.byId("btnNewLicense_" + userApsId).cancel();
                    displayMessage(_("There are not available licenses"), "warning");
                }
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
            
            /**
             * Destroys the license box for avoiding id duplicating
             * @param {string} userApsId
             */
            var hideLicenseBox = function (userApsId) {
                // Destroys the container (deletes all the widget within)
                registry.byId("cntViewLicense_" + userApsId).destroyRecursive();
                // Stops the waiting state of the button
                registry.byId("btnNewLicense_" + userApsId).cancel();
            };
            
            /**
             * Creates a license in the user with aps id from the param
             * @param {string} userApsId
             */
            var createLicense = function (userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // Prepares license model from json
                    var licenseModel = JSON.parse(json_UserLicense);
                    licenseModel.licenseNum = "1";
                    licenseModel.quota = registry.byId("qsLicense_" + userApsId).value;
                    licenseModel.licenseType = registry.byId("rbDesktop_" + userApsId).get("checked") ? "Desktop" : "Server";
                    licenseModel.user_group_id = registry.byId("selUserGroup_" + userApsId).get("value");
                    // Check that quota is not 0
                    if (licenseModel.quota > 0) {
                        // Choose counter depending on type
                        var licenses = (licenseModel.licenseType === "Desktop") ? mozyAccount.desktopLicenseNum : mozyAccount.serverLicenseNum;
                        // Check the available licenses
                        var availableLicenses = licenses.limit - ((licenses.usage) ? licenses.usage : 0);
                        if (availableLicenses > 0) {
                            // Prepare Store for licenses
                            var storeNewLic = new Store({
                                target: "/aps/2/resources/" + userApsId + "/mozyProAccountUserLicense"
                            });
                            // Request to server
                            storeNewLic.put(licenseModel).then(function () {
                                // Refresh the page
                                refreshPage(_("The license has been created successfully."), "info");
                            }).otherwise(function (errorCreatingLicense) {
                                console.log("There is an error in creating the license");
                                displayErrorMessage(errorCreatingLicense, _("User license could not be created."), "btnCreateLicense_" + userApsId);
                            });
                        // No licenses available
                        } else { displayErrorMessage('', _("Not enough licenses available"), "btnCreateLicense_" + userApsId); }
                    // Quota has to be greater than 0
                    } else { displayErrorMessage('', _("License quota must be greater than 0."), "btnCreateLicense_" + userApsId); }
                // A process is still working
                } else { busyWarning(); }
            };
            
            /**
             * Remove a user license
             * @param {object} licenseModel
             * @param {string} userApsId
             */
            var removeLicense = function (licenseModel, userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // If there is an existing license box, it will be destroyed it
                    if (registry.byId("cntViewLicense_" + userApsId)) registry.byId("cntViewLicense_" + userApsId).destroyRecursive();
                    // Prepare the store for deleting
                    var deleteLicenseStore = new Store({target: "/aps/2/resources/"});
                    // Request to the server
                    deleteLicenseStore.remove(licenseModel.aps.id).then(function () {
                        // Refresh the page and displays a message
                        refreshPage(_("The license has been eliminated."), "info");
                    // Error removing the license
                    }).otherwise(function (errorLicenseDelete) {
                        console.log("There is an error in deleting the license");
                        displayErrorMessage(errorLicenseDelete, _("User licenses could not be eliminated."));
                    });
                // A process is still working
                } else { busyWarning(); }
            };
    
    // __ End of License Management method
            
    // User Management methods
            /**
             * Disable the user
             * @param {string} userApsId
             */
            var disableUser = function (userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // If there is a license box displayed destroy it
                    if (registry.byId("cntViewLicense_" + userApsId)) registry.byId("cntViewLicense_" + userApsId).destroyRecursive();
                    // Request to the server
                    xhr("/aps/2/resources/" + userApsId + "/disableuser", {method: "POST"}).then(function () {
                        // Refresh the page and displays a message
                        refreshPage(_("The user is suspended."), "info");
                    // Error in disabling the user
                    }).otherwise(function (errorDisablingUser) {
                        console.log("There is an error in disableuser method");
                        displayErrorMessage(errorDisablingUser, errorDisablingUser, "btnDisable_" + userApsId);
                    });
                // A process is still working
                } else {
                    busyWarning();
                    registry.byId("btnDisable_" + userApsId).cancel();
                }
            };
            
            /**
             * Enable the user
             * @param {string} userApsId
             */
            var enableUser = function (userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // Request to the server
                    xhr("/aps/2/resources/" + userApsId + "/enableuser", {method: "POST"}).then(function () {
                        // Refresh the page and displays a message
                        refreshPage(_("The user is activated."), "info");
                    }).otherwise(function (errorEnablingUser) {
                        console.log("There is an error in enableuser method");
                        displayErrorMessage(errorEnablingUser, errorEnablingUser, "btnEnable_" + userApsId);
                    });
                // A process is still working
                } else {
                    busyWarning();
                    registry.byId("btnEnable_" + userApsId).cancel();
                }
            };
            
            /**
             * Removes a user from the Customer
             * @param {string} userApsId
             */
            var removeUser = function (userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // Request to licenseRemover method to remove all the licenses
                    _licenseRemover(userApsId).then(function () {
                        // Prepares Store to remove the user
                        var deleteUserStore = new Store({ target: "/aps/2/resources/" });
                        // Request to server
                        deleteUserStore.remove(userApsId).then(function () {
                            // Refresh the page and displays a message
                             refreshPage(_(""), "info");
                        // Error removing the user
                        }).otherwise(function (removeUserError) {
                            console.log("There is an error in removing the user");
                            displayErrorMessage(removeUserError, _("The user could not be removed."), "btnRemove_" + userApsId);
                        });
                    // Error in the licenses remove
                    }).otherwise(function (RemoverError) {
                        console.dir(RemoverError);
                        displayErrorMessage(RemoverError, _("User licenses could not be eliminated."), "btnRemove_" + userApsId);
                    });
                // A process is still working
                } else {
                    busyWarning();
                    registry.byId("btnRemove_" + userApsId).cancel();
                }
            };
            
            /**
             * Recursive function
             * For timing problems, it sends request waiting for the server ok to send the next one
             * @param {object} userArray One license will be created for each user of the array
             * @param {void} counter Needed for recursive functionality
             * @param {void} deferred Needed for recursive functionality
             * @returns {Deferred.promise|undefined}
             */
            var assignBulkLicenses = function (userArray, counter, deferred) {
                // The first iteration create the deferred object
                if (!deferred) deferred = new Deferred();
                // The first iteration set the control counter
                if (!counter) counter = 0;
                // Exit of the function previous to operations
                if (counter === userArray.length) {
                    deferred.resolve();
                    return;
                }
                // Prepares a empty license model
                var licenseModel = JSON.parse(json_UserLicense);
                // Associate information from the slider
                licenseModel.licenseNum = "1";
                licenseModel.quota = registry.byId("qsLicense_new").value;
                licenseModel.licenseType = registry.byId("rbDesktop_new").get("checked") ? "Desktop" : "Server";
                licenseModel.user_group_id = aps.context.vars.mozyProAccount.user_group_id;
                // Prepare the Store to AccountUserLicenses
                var storeUsersLic = new Store({
                    target: "/aps/2/resources/" + userArray[counter].aps.id + '/mozyProAccountUserLicense'
                });
                // Request one license to the server
                storeUsersLic.put(licenseModel).then(function () {
                    // Control counter adds up
                    counter +=1;
                    // Recursion sending deferred object and counter for avoiding infinite loops
                    assignBulkLicenses(userArray, counter, deferred);
                // Error creating one license breaks the flow
                }).otherwise(function (errorCreatingLicense) {
                    deferred.cancel(errorCreatingLicense);
                });
                // Returns the promise to the parent function
                return deferred.promise;
            };
    // __ End of User Management methods

    // Sync Quota methods
            /**
             * Enables or Disables the Sync service for a user
             * @param {string} userApsId
             * @param {number} enableSwitch 0/1
             */
            var userSyncSwitcher = function (userApsId, enableSwitch) {
                // Prepares id of the button clicked
                var btnClicked = (enableSwitch === 1) ? "btnSyncOn_": "btnSyncOff_";
                if (!workingProcess) {
                    initProcess();
                    // Prepares Store to Account User
                    var userStore = new Store({
                        target: "/aps/2/resources/",
                        type: "http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1"
                    });
                    // Request to server Get the user with the APS id
                    userStore.get(userApsId).then(function (userModel) {
                        // Change the model with the new value for Sync
                        userModel.enableSync = enableSwitch;
                        // Request to server to update the user
                        userStore.put(userModel).then(function () {
                            // Refresh the page
                            refreshPage();
                        // Error updating the user
                        }).otherwise(function (errorUpdatingUser) {
                            if (errorUpdatingUser.status === 410) {
                                displayMessage(_('The user could not active Sync without an active license.'), "error");
                                registry.byId(btnClicked + userApsId).cancel();
                            } else if (errorUpdatingUser.status === 411) {
                                displayMessage(_('The user could not active Sync with the available Desktop quota.'), "error");
                                registry.byId(btnClicked + userApsId).cancel();
                            } else {
                                displayErrorMessage(errorUpdatingUser, errorUpdatingUser, btnClicked + userApsId);
                            }
                            endProcess();
                        });
                    // Error retrieving a user
                    }).otherwise(function (errorRetrievingUser) {
                        displayErrorMessage(errorRetrievingUser, errorRetrievingUser, btnClicked + userApsId);
                    });
                // A process is still working
                } else {
                    busyWarning();
                    registry.byId(btnClicked + userApsId).cancel();
                }
            };
            
            /**
             * Updates the sync quota
             * @param {string} userApsId
             */
            var editSyncQuota = function (userApsId) {
                if (!workingProcess) {
                    initProcess();
                    // Prepares Store to Account User
                    var userStore = new Store({
                        target: "/aps/2/resources/",
                        type: "http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1"
                    });
                    // Request to server Get the user with the APS id
                    userStore.get(userApsId).then(function (userModel) {
                        // Change the model with the new value for Sync
                        userModel.syncQuota = registry.byId("qsLicense_" + userApsId).get("value");
                        // Request to server to update the user
                        userStore.put(userModel).then(function () {
                            // Refresh the page
                            refreshPage(_("Sync quota has been updated"), "info");
                        // Error updating the user
                        }).otherwise(function (errorUpdatingUser) {
                            displayErrorMessage(errorUpdatingUser, errorUpdatingUser, "btnUpdateSync_" + userApsId);
                        });
                    // Error retrieving a user
                    }).otherwise(function (errorRetrievingUser) {
                        displayErrorMessage(errorRetrievingUser, errorRetrievingUser, "btnUpdateSync_" + userApsId);
                    });
                // A process is still working
                } else {
                    busyWarning();
                    registry.byId("btnUpdateSync_" + userApsId).cancel();
                }
            };
    // __ End of Sync Quota methods

            /**
             * Check if the subscription can use Sync
             * @returns {Boolean}
             */
            var isSyncAvailable = function () {
                var sync = aps.context.vars.mozyProAccount;
                var ready = false;
                if (sync.syncAvailable) {
                    if (sync.syncAvailable.limit > 0) {
                        if (sync.syncAvailable.usage > 0) ready = true;
                    }
                }
                return ready;
            };

            /**
             * Round a value in Bytes to a value in Gigas with two decimals
             * @param {Number} valueInBytes
             * @returns {Number}
             */
            var toGigas = function (valueInBytes) {
                for (var i = 0; i < 3; i++) {
                    valueInBytes = valueInBytes/1024;
                }
                valueInBytes = Math.round(valueInBytes*100);
                return valueInBytes/100;
            };

            /**
             * Refresh the page and left a message for the next view
             * @param {string} message
             * @param {string} type
             */
            var refreshPage = function (message, type) {
                if (message && type) setCacheMessage(message, type);
                parent.document.getElementById("path_select1").onclick();
            //    window.location.reload(true);
            //    parent.document.getElementById("path_select1").onclick();
            };

    // Page Widgets
            // Contexts
            var mozyAccount = aps.context.vars.mozyProAccount;

            // Store to Mozy Users
            var usersStore = new Store({
                target: "/aps/2/resources/?implementing(http://www.mozy.com/mozyProAPS2/mozyProAccountUser/1.1),eq(userId,"+aps.context.params.user.userId+")"
            });
            // First Container - Summary
            var newUserContainer = ["aps/Container", {id: "newUserGeneralContainer"},
                [
                //    ["aps/Output", {innerHTML: _("Here you can list your users and assign new licenses to the users.<br><br/>")}]
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

                all(arrLicenses).then(function (licensesPerUser) {
                    // Prepare Active Items array
                    var activeItems = [];
                //    activeItems.push(["aps/Button", {id: "newUserButton", title: _("Create new User"), onClick: function () { showUserBox(userList);} }]);
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

                        // Create Active Item for the user
                        var contGroup = ["aps/Container", { label: _("Group"), visible: (mozyAccount.accountType === "CR") ? false : true },
                            [
                                ["aps/Select", {
                                    id: "selUserGroup_" + model.aps.id,
                                    title: _("Group"),
                                    value: model.user_group_id,
                                    visible: (model.userstatus === "active") ? true : false,
                                    options: userGroupsOptions
                                }],
                                ["aps/Button", {
                                    id: "btnUpdateUserGroup_" + model.aps.id,
                                    label: _("Update"),
                                    data: model.aps.id,
                                    style: "margin-left: 5px; height: 21px; margin-top:1px",
                                    visible: (model.userstatus === "active") ? true : false,
                                    onClick: function() { changeUserGroup(this.data, userGroupsOptions); }
                                }],
                                ["aps/Output", {
                                    id: "outUserGroup_" + model.aps.id,
                                    value: (userGroup) ? ((userGroup === "Default") ? _("Default") : userGroup) : "",
                                    visible: (model.userstatus === "active") ? false : true
                                }]
                            ]];
                        // Container with user data
                        var contUserData = ["aps/Container", {title: _("User data")},
                            [
                                    ["aps/FieldSet",
                                        [["aps/Output", {label: _("E-Mail"), innerHTML: model.login}], contGroup ]
                                    ]
                            ]];

                    // Methods for licenses Grid
                        /**
                         * Render Cell (licenses grid)
                         * Displays quota information in relation used / total
                         * @param {object} row license model
                         * @returns {String}
                         */
                        var rcStorage = function (row) {
                            var usedQuota = row.quota_used_bytes;
                            var cell = row.quota + " GB";
                            if (usedQuota) {
                                cell = toGigas(usedQuota) + " / " + row.quota + " GB";
                            }
                            return cell;
                        };
                        /**
                         * Render Cell (licenses grid)
                         * Displays a link to edit the license quota
                         * @param {object} row license model
                         * @returns {aps/Output}
                         */
                        var rcEditStorage = function (row) {
                            var userApsId = this.data;
                            var opEdit = new Output({
                                style: "cursor:pointer",
                                innerHTML: "<a src'#'>" + _("Edit") + "</a>",
                                onClick: function () { showUpdateLicenseBox(row, userApsId); }
                            });
                            return opEdit;
                        };
                        /**
                         * Render Cell (licenses grid)
                         * Displays information about last backup date
                         * @info {format} date "2015-04-03T00:00:00-06:00"
                         * @param {object} row license model
                         * @returns {aps/Output}
                         */
                        var rcLastUpdate = function (row) {
                            var lastUpdate = row.last_backup_at;
                            var cell = "";
                            if (lastUpdate) {
                                var jsonDate = timeTilNow(lastUpdate);
                                cell = new Output({ value: jsonDate.diff, innerHTML: jsonDate.message });
                            }
                            return cell;
                        };
                        /**
                         * Render Cell (licenses grid)
                         * Displays a link to remove the license
                         * @param {object} row license model
                         * @returns {aps/Output}
                         */
                        var rcRemoveLicense = function (row) {
                            var userApsId = this.data;
                            var opRemove = new Output({
                                style: "cursor:pointer",
                                innerHTML: "<a src'#'>" + _("Remove License") + "</a>",
                                onClick: function () { removeLicense(row, userApsId); }
                            });
                            return opRemove;
                        };
                        /**
                         * Fix: i18n catch license type
                         * @param {object} row license model
                         * @returns {string}
                         */
                        var rcLicenseType = function (row) {
                            var type = row.licenseType;
                            var response = type;
                            if (type === "Desktop") { response = _("Desktop"); } else
                            if (type === "Server") { response = _("Server"); }
                            return response;
                        };
                        // License Grid Container
                        var contUserLicenses = ["aps/Container", {title: _("Licenses")},
                            [
                                ["aps/Grid", {
                                        id: "gridLicenses_" + model.aps.id,
                                        showPaging: false,
                                        columns: [
                                            {"name": _('Keystring'), "field": "keyString"},
                                            {"name": _('Type'), renderCell:rcLicenseType},
                                            {"name": _("Computer"), "field": "alias"},
                                            {"name": _("Storage"), "field": "quota_used_bytes", renderCell:rcStorage},
                                            {"name": " ", "data": model.aps.id, "visible": (model.userstatus === "active" && (userGroup === "Default" || mozyAccount.accountType === "CR")) ? true : false, renderCell: rcEditStorage},
                                            {"name": _("Last Update"), "field": "last_backup_at", renderCell:rcLastUpdate},
                                            {"name": " ", "data": model.aps.id, "visible": (model.userstatus === "active" && (userGroup === "Default" || mozyAccount.accountType === "CR")) ? true : false, renderCell: rcRemoveLicense}
                                        ],
                                        store: new Memory({data: licensesPerUser[i], idProperty: "aps.id"})
                                    }
                                ]
                            ]];
                    // _
                    // Methods for Sync grid
                        /**
                         * Render Cell (sync grid)
                         * Displays sync information in relation used / total
                         * @param {object} row user model
                         * @returns {String}
                         */
                        var rcSyncStorage = function (row) {
                            var response = row.syncQuotaUsedMozy + " / " + row.syncQuota + " GB";
                            return response;
                        };
                        /**
                         * Render Cell (sync grid)
                         * Displays a link to edit the sync quota
                         * @param {object} row user model
                         * @returns {aps/Output}
                         */
                        var rcEditSyncStorage = function (row) {
                            var userApsId = this.data;
                            var opEdit = new Output({
                                style: "cursor:pointer",
                                innerHTML: "<a src'#'>" + _("Edit") + "</a>",
                                onClick: function () {
                                    showUpdateSyncBox(userApsId, row.syncQuota);
                                }
                            });
                            return opEdit;
                        };
                        /**
                         * Render Cell (sync grid)
                         * Displays information about last backup date
                         * @info {format} date "2015-04-03T00:00:00-06:00"
                         * @param {object} row user model
                         * @returns {aps/Output}
                         */
                        var rcSyncLastUpdate = function (row) {
                            var lastUpdate = row.syncLastUpdate;
                            var cell = "";
                            if (lastUpdate) {
                                var date = timeTilNow(lastUpdate);
                                cell = new Output({ value: date.diff, innerHTML: date.message });
                            }
                            return cell;
                        };
                        /**
                         * Render Cell (sync grid)
                         * Displays a link to remove the sync
                         * @info {format} date "2015-04-03T00:00:00-06:00"
                         * @param {object} row user model
                         * @returns {aps/Output}
                         */
                        var rcRemoveSync = function () {
                            var userApsId = this.data;
                            var opRemove = new Output({
                                style: "cursor:pointer",
                                innerHTML: "<a src'#'>" + _("Remove Sync") + "</a>",
                                onClick: function () {
                                    userSyncSwitcher(userApsId, 0);
                                }
                            });
                            return opRemove;
                        };
                        // Sync grid Container
                        var contUserSync = ["aps/Container",
                            {
                                id: "contSync_" + model.aps.id,
                                title: _("Sync"),
                                visible: (isSyncAvailable() && model.enableSync && model.enableSync > 0) ? true : false
                            },
                            [
                                ["aps/Grid", {
                                        id: "gridSync_" + model.aps.id,
                                        showPaging: false,
                                        columns: [
                                            {"name": _("Storage"), renderCell: rcSyncStorage},
                                            {"name": " ", "data": model.aps.id, "visible": (userGroup === "Default" || mozyAccount.accountType === "CR") ? true : false, renderCell: rcEditSyncStorage},
                                            {"name": _("Last Update"), renderCell: rcSyncLastUpdate},
                                            {"name": " ", "data": model.aps.id, "visible": (userGroup === "Default" || mozyAccount.accountType === "CR") ? true : false, renderCell: rcRemoveSync}
                                        ],
                                        store: new Memory({data: [model], idProperty: "aps.id"})
                                    }
                                ]
                            ]];
                    // _
                    // Active item for each user
                        var activeItem = ["aps/ActiveItem",
                            {
                                id: "activeItem_" + model.aps.id,
                                title: (model.userstatus === "suspended") ? model.displayName + _(" (suspended)"): model.displayName,
                                collapsible:true,
                                description: (mozyAccount.accountType === "C") ? _("Group") + ": " + ((userGroup === "Default") ? _("Default") : userGroup) : ""
                            },[
                                ["aps/ToolbarButton", {
                                        id: "btnEnable_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Activate User"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-turn-on",
                                        visible: (model.userstatus === "active") ? false: true,
                                        onClick: function() { enableUser(this.data); }
                                        }],
                                ["aps/ToolbarButton", {
                                        id: "btnDisable_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Suspend User"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-turn-off",
                                        visible: (model.userstatus === "active") ? true: false,
                                        onClick: function() { disableUser(this.data); }
                                        }],
                                ["aps/ToolbarButton", {
                                        id: "btnRemove_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Remove User"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-item-remove",
                                        visible: (expandedInterface(model.user_group_id)) ? true: false,
                                        onClick: function() { removeUser(this.data); }
                                        }],
                                ["aps/ToolbarButton", {
                                        id: "btnNewLicense_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Add New License"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-add-mail-list",
                                        visible: (model.userstatus === "active" && expandedInterface(model.user_group_id)) ? true: false,
                                        onClick: function() { showLicenseBox(this.data); }
                                        }],
                                ["aps/ToolbarButton", {
                                        id: "btnSyncOn_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Enable Sync"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-check-updates",
                                        visible: (isSyncAvailable() && model.userstatus === "active" && expandedInterface(model.user_group_id) && !model.enableSync) ? true: false,
                                        onClick: function () { userSyncSwitcher(this.data, 1); }
                                        }],
                                ["aps/ToolbarButton", {
                                        id: "btnSyncOff_" + model.aps.id,
                                        data: model.aps.id,
                                        label: _("Disable Sync"),
                                        busyLabel: _("Please wait"),
                                        iconClass: "sb-delete",
                                        visible: (isSyncAvailable() && model.userstatus === "active" && expandedInterface(model.user_group_id) && model.enableSync) ? true: false,
                                        onClick: function () { userSyncSwitcher(this.data, 0); }
                                        }],
                                    contUserData,
                                    contUserLicenses,
                                    contUserSync
                            ]];
                        activeItems.push(activeItem);
                    }

                    // List Switcher - Grid view
                    
                    /**
                     * Generate a Memory with data from each user including the licenses types for the grid
                     * @returns {aps/Memory}
                     */
                    var getMemoryLicensesPerUser = function () {
                        // Array of objects with data combined from user and his licenses
                        var completeUser= [];
                        // For each user look for his licenses
                        for (var i = 0; i < userList.length; i++) {
                            // Prepare an object for combined data
                            var objUser = {};
                            objUser = userList[i];
                            // User enable / disable sync status
                            objUser.syncStatus = (userList[i].enableSync === 0) ? _("Disabled"): _("Enabled");
                            // User group name (not id)
                            for (var j = 0; j < groupList.length; j++) {
                            if (String(groupList[j].groupId) === String(userList[i].user_group_id))
                                objUser.groupName = groupList[j].name;
                            }
                            // User licenses counters
                            objUser.desktopAssigned = 0;
                            objUser.desktopActivated = 0;
                            objUser.serverAssigned = 0;
                            objUser.serverActivated = 0;
                            // For each license check the type and activation
                            for (var k = 0; k < licensesPerUser[i].length; k++) {
                                var userLicense = licensesPerUser[i];
                                if (userLicense[k].licenseType === "Desktop") {
                                    (userLicense[k].alias === "") ? objUser.desktopAssigned += 1: objUser.desktopActivated += 1;
                                } else {
                                    (userLicense[k].alias === "") ? objUser.serverAssigned += 1: objUser.serverActivated += 1;
                                }
                            }
                            // Include in the array for the memory
                            completeUser.push(objUser);
                        }
                        // Returns the Memory with complete data
                        return new Memory ({data:completeUser, idProperty: "aps.id" });
                    };
                    
                    // Grid with the list of users for the List Switcher
                    var contUsersGrid = ["aps/Grid",
                        {
                            columns: [
                                {name: _("Name"), field: "displayName", "filter": {"title": _("Name")}},
                                {name: _("Email"), field: "login", "filter": {"title": _("Email")}},
                                {name: _("Group"), field: "groupName"},
                                {name: _("Desktop Assigned"), field: "desktopAssigned"},
                                {name: _("Desktop Activated"), field: "desktopActivated"},
                                {name: _("Server Assigned"), field: "serverAssigned"},
                                {name: _("Server Activated"), field: "serverActivated"},
                                {name: _("Sync"), field: "syncStatus"}
                            ],
                            store: getMemoryLicensesPerUser()
                        }
                    ];

                    // List Switcher 
                    var listSwitcher = ["aps/ListSwitcher", { id: "listSwitcher" },
                                        [
                                            ["aps/ActiveList", activeItems],
                                            contUsersGrid
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

                });
            });

        });
