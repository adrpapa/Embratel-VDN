/**
 * View refactorized
 * 
 * @parent mozyProAccount.groups.list.html
 * @param {aps/load} load
 */
require(["aps/load", "dijit/registry", "aps/ResourceStore", "dojox/mvc/getStateful", "aps/Gauge", "aps/Output", "aps/Message", "dojo/when", "dojo/Deferred", "aps/xhr", "aps/Memory", "aps/ready!"],
        function (load, registry, Store, getStateful, Gauge, Output, Message, when, Deferred, xhr, Memory) {
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
             * Returns an aps/gauge created depending on parameters
             * @param {string} titleData
             * @param {string} legendData
             * @param {number} maximumData
             * @param {string} valueData
             * @returns {aps/gauge}
             */
            var getGauge = function (titleData, legendData, maximumData, valueData) {
                var gauge = ["aps/Gauge", {
                        title: titleData,
                        legend: legendData,
                        minimum: 0,
                        maximum: maximumData,
                        value: valueData,
                        width: "65%"
                    }
                ];
                var classesMap = {};
                    classesMap[maximumData*0.5] = "warn";
                    classesMap[maximumData*0.75] = "over";
                    gauge[1].classesMap = classesMap;
                return gauge;
            };
            
            /**
             * Returns an aps/gauge for a renderCell
             * @param {string} title
             * @param {string} legend
             * @param {number} maximum
             * @param {string} value
             * @returns {aps/Gauge}
             */
            var getGaugeCell = function(title, legend, maximum, value) {
                var classesMap = {};
                    classesMap[maximum*0.5] = "warn";
                    classesMap[maximum*0.75] = "over";
                return new Gauge({title: title, legend: legend, minimum: 0, maximum: maximum, value: value, width:"65%", classesMap: classesMap});
            };
            
            /**
             * Returns the percentage between the value and maximum received from parameters
             * @param {type} value
             * @param {type} maximum
             * @returns {String}
             */
            var percent = function(value, maximum) {
                var percentage = 0;
                if (maximum !== 0) {
                    percentage = value * 100 / maximum;
                }
                return percentage.toFixed(2) + "%";
            };

            // Contexts
            var mozyAccount = aps.context.vars.mozyProAccount;

            // If partnerId is null, it is a repeated subscription, else, we do the normal workflow
            if(mozyAccount.partnerId === null)
            {
                var GetStore = new Store({
		target : "/aps/2/resources/?implementing(http://parallels.com/aps/types/pa/admin-user/1.0)"
                });
                var getXhr = function(){
                var deferred = new Deferred();
                when(GetStore.query(), function(GetStore){
                        var item = null;
                        var json = JSON.parse(item);
                        var result = [];
                        var container = { };
                        
                        // Extract to JSON
                        item = JSON.stringify(GetStore);
                        json = JSON.parse(item);
                        result = [];
                        container = [];
                        for (var i = 0; i < json.length; i++){
                                // Put Email into Array
                                result.push(json[i].email);
                                var jsonString = {"id":json[i].aps.id,"email":json[i].email,"displayname":json[i].displayName};
                                container.push(jsonString);
                        }
                        var resultA = JSON.stringify(result);
                        
                        // Call to TENANT to veryfy emails
                        when(xhr("/aps/2/resources/" + mozyAccount.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: resultA}}),
                            function (res) {
                                var result = [];
                                if (res !== "500" && res !== 500 && res !== "513" && res !== 513){
                                    var xRes = JSON.parse(res);
                                    for (var i = 0; i < xRes.length; i++){
                                        for (var j = 0; j < container.length; j++){
                                            var tmp = JSON.stringify(container[j]);
                                            var index = tmp.indexOf(xRes[i]);
                                            if (index > -1) {
                                                    result.push(container[j]);
                                            }
                                        }
                                    }
                                }
                                deferred.resolve(result);
                            }
                        );
                }, function(error){
                        deferred.cancel(error);
                });
                return deferred.promise;
            };

            var handleEvents = function() {
                    var grid = registry.byId("mozy_grid");

                    var rowCount = registry.byId("mozy_grid").get("_totalItemCount");
                    if (rowCount < 10) {
                            grid.set("showPaging", false);
                    }
                    if (rowCount === 0){
                            registry.byId("assignEmail").set("visible",false);
                    }

            };

            when(getXhr(), function(str) {
                    var storeGrid = new Memory({data: str, idProperty: "id"});
                    var pageContainer = ["aps/PageContainer", {
                        id: "page"
                        },
                        [
                            ["aps/FieldSet", {
                                    escapeHTML: false,
                                    description: "<b>"+_("Error:")+"</b> "+_("The staff member's email used to create the subscription is already in use.")+" <br/><br/>"+_("To solve this error and create the subscription and Admin User correctly, you must:")+"<ol style='list-style-type:decimal;list-style-position:inside;margin:auto;padding-left:40px;'><li>"+_("Create a staff member (Account > Admins > Add New Admin) with an email that has not been used before.")+"</li><li>"+_("In the APS package tab, select this new staff member and associate it with the Service.")+"</li></ol>"
                            }],
                            ["aps/Grid", {
                                        id: "mozy_grid",
                                        selectionMode: "single",
                                        columns: [{
                                                field: "displayname",
                                                name: _("Name")
                                        }, {
                                                field: "email",
                                                name: _("Email Admin User")
                                        }],
                                        store: storeGrid
                                    }
                            ],
                            ["aps/Container", {
                                    showLabels: false,
                                    cols: 2,
                                    "class": "twocolums"
                            },[
                                ["aps/FieldSet", {
                                        showLabels: false
                                },[
                                    ["aps/Button",{
                                            id: "assignEmail",
                                            title: _('Assign Staff Member to the Service'),
                                            onClick: function(){
                                                var page = registry.byId("page");
                                                var grid = registry.byId("mozy_grid");
                                                var asemail = registry.byId("assignEmail");
                                                var sel = grid.get("selectionArray");
                                                var counter = sel.length;
                                                var guid ="";
                                                var emailA = null;
                                                // if counter <1 exit because not have selection
                                                if (counter<1){
                                                        page.get("messageList").removeAll();
                                                        page.get("messageList").addChild(new Message({
                                                                description: _("You have not selected any Staff Members, please select one."),
                                                                type: "error"
                                                        }));
                                                        aps.apsc.cancelProcessing();
                                                        asemail.cancel();
                                                        return;
                                                }
                                                var idSelectItem = registry.byId("mozy_grid").get("selectionArray")[0];
                                                var dataStoreGrid = storeGrid.data;
                                                dataStoreGrid.forEach(function(items){
                                                        
                                                        // The original way is "items.aps.id"
                                                        if(items.id === idSelectItem){
                                                                emailA = [items.email];
                                                                guid = idSelectItem;
                                                        }
                                                });
                                                // convert array to JSON
                                                emailA = JSON.stringify(emailA);
                                                when(xhr("/aps/2/resources/" + mozyAccount.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: emailA}}),
                                                    function (res) {
                                                            if (res === "500" || res === 500){
                                                                    page.get("messageList").removeAll();
                                                                    page.get("messageList").addChild(new Message({
                                                                            description: _("Connection with Mozy Endpoint has failed."),
                                                                            type: "error"
                                                                    }));
                                                                    aps.apsc.cancelProcessing();
                                                                    asemail.cancel();
                                                                    return;
                                                            }
                                                            else if (res === "513" || res === 513){
                                                                    page.get("messageList").removeAll();
                                                                    page.get("messageList").addChild(new Message({
                                                                            description: _("This Staff Member email already exists at Mozy. Please, choose another one."),
                                                                            type: "error"
                                                                    }));
                                                                    aps.apsc.cancelProcessing();
                                                                    asemail.cancel();
                                                                    return;
                                                            }
                                                            else{
                                                                page.get("messageList").removeAll();
                                                                page.get("messageList").addChild(new Message({
                                                                        description: _("Provisioning process might take a while."),
                                                                        type: "progress"
                                                                }));
                                                                when(xhr("/aps/2/resources/" + mozyAccount.aps.id + "/createPartner", {method: "POST", handleAs: "text", query: {staffGUID: guid}}),
                                                                    function () {
                                                                        page.get("messageList").removeAll();
                                                                        page.get("messageList").addChild(new Message({
                                                                                description: _("Create admin user ok."),
                                                                                type: "update"
                                                                        }));
                                                                        asemail.cancel();
                                                                        parent.document.getElementById("refresh_action").onclick();
                                                                        return;
                                                                }
                                                            );
                                                        }
                                                    },
                                                    function (error) {
                                                    page.get("messageList").removeAll();
                                                    page.get("messageList").addChild(new Message({
                                                            description: _(error),
                                                            type: "error"
                                                    }));
                                                    aps.apsc.cancelProcessing();
                                                    asemail.cancel();
                                                    return;
                                                }
                                            );
                                        }
                                    }]]
                                ]]
                            ]
                        ]];
                    when(load(pageContainer), handleEvents);
		});
            }
            else
            {
                            // Store to Mozy Groups
                var groupsStore = new Store({
                    target: "/aps/2/resources/" + mozyAccount.aps.id + "/mozyProAccountGroup/"
                });

                // First Container - headline Output
                var outputHeadline = ["aps/Output", {innerHTML: _("Here you list your groups and can create new ones.<br><br/>")}];

                // Preparing ActiveItems array for the Active List. Include the Add New Group Button.
                var activeItems = [];
                activeItems.push(["aps/Button", {title: _("Add New Group"), id:"newGroupButton"}]);

                groupsStore.query().then(function (groupList) {
                    // For each group
                    for (var i = 0; i < groupList.length; i++) {
                        // model declaration with groups received from Query
                        var model = getStateful(groupList[i]);
                        // Store with users from a group
                        var storeUsersList = new Store({
                            target: "/aps/2/resources/" + mozyAccount.aps.id + "/mozyProAccountUser?eq(user_group_id," + model.groupId + ")"
                        });

                        // Infoboard with gauges depending of keys ordered by type
                        var infoBoard = [];
                        if (model.desktopKeysOrdered > 0) {
                            infoBoard.push (getGauge(_("Desktop Quota: ${percent}"), _("${value} GB used of ${maximum} GB"),model.desktopQuotaOrdered, model.desktopQuotaAssigned));
                            infoBoard.push (getGauge(_("Desktop Keys: ${percent}"), _("${value} units used of ${maximum} units"),model.desktopKeysOrdered, model.desktopKeysAssigned));
                        }
                        if (model.serverKeysOrdered > 0) {
                            infoBoard.push (getGauge(_("Server Quota: ${percent}"), _("${value} GB used of ${maximum} GB"),model.serverQuotaOrdered, model.serverQuotaAssigned));
                            infoBoard.push (getGauge(_("Server Keys: ${percent}"), _("${value} units used of ${maximum} units"),model.serverKeysOrdered, model.serverKeysAssigned));
                        }

                        // Grid for each group with the list of users
                        var contUsersGrid = ["aps/Grid",
                            {
                                id: "gridDetail_" + model.groupId,
                                columns: [
                                    {name: _("Name"), field: "displayName", "filter": {"title": _("Name")}},
                                    {name: _("Email"), field: "login", "filter": {"title": _("Email")}},
                                    {name: _("Server Licenses"), field: "serverLicSum"},
                                    {name: _("Server Quota"), field: "serverQuotaSum"},
                                    {name: _("Desktop Licenses"), field: "desktopLicSum"},
                                    {name: _("Desktop Quota"), field: "desktopQuotaSum"}
                                ],
                                store: storeUsersList
                            }
                        ];

                        // Active Item for the group
                        var activeItemGroup = ["aps/ActiveItem",
                            {
                                title: (model.name === "Default") ? _("Default") : model.name,
                                collapsible:true,
                                description: new Output({ innerHTML: "Id: ${value}", value: model.groupId})
                            },
                            [
                                // Button Edit - For each group but Default
                                ["aps/ToolbarButton",
                                    {
                                        label: _("Edit"),
                                        id:"ebg_" + model.aps.id,
                                        visible: (model.name === "Default") ? false: true,
                                        iconName: "images/edit_16x16.gif",
                                        onClick: function () {
                                            aps.apsc.gotoView("mozyProAccount.groups.edit", this.id.substr(4));
                                        }
                                    }
                                // Button Delete - For each group but Default
                                ], ["aps/ToolbarButton",
                                    {
                                        label: _("Delete"),
                                        id:"rbg_" + model.aps.id,
                                        visible: (model.name === "Default") ? false: true,
                                        iconName: "images/delete_16x16.gif",
                                        onClick: function () {
                                            var message = _("Do you want confirm to remove the Group?");
                                            if (confirm(message)) {
                                                groupsStore.remove(this.id.substr(4)).then(function () {
                                                    aps.apsc.gotoView("mozyProAccount.groups.list");
                                                }). otherwise(function (errorRemovingGroup) {
                                                    console.log("The store cannot remove this item");
                                                    console.dir(errorRemovingGroup);
                                                    displayMessage("The group cannot be deleted", "error");
                                                });
                                            } else {
                                                registry.byId("rbg_" + model.aps.id).cancel();
                                            }
                                        }
                                    }
                                ],
                                    ["aps/InfoBoard", {cols: 4}, infoBoard],
                                    ["aps/Container", { id:"cgd_" + model.groupId, visible:true}, [contUsersGrid]]
                                ]
                            ];
                        // Add the ActiveItem to the Array
                        activeItems.push(activeItemGroup);
                    }

                    // Grid with the groups
                    var gridGroups = ["aps/Grid",
                        {
                            columns: [
                                {name: _("GroupId"), field: "groupId", "filter": {"title": _("GroupId")}},
                                {name: _("Name"), field: "name", "filter": {"title": _("Name")}},
                                {name: _("Server Licenses"), field: "serverKeysOrdered", renderCell: function (object) {
                                        return getGaugeCell(_("Server Keys: ") + percent(object.serverKeysAssigned, object.serverKeysOrdered), _("${value} units used of ${maximum} units"), object.serverKeysOrdered, object.serverKeysAssigned);
                                    }},
                                {name: _("Server Quota"), field: "serverQuotaOrdered", renderCell: function (object) {
                                        return getGaugeCell(_("Server Quota: ") + percent(object.serverQuotaAssigned, object.serverQuotaOrdered), _("${value} GB used of ${maximum} GB"), object.serverQuotaOrdered, object.serverQuotaAssigned);
                                    }},
                                {name: _("Desktop Licenses"), field: "desktopKeysOrdered", renderCell: function (object) {
                                        return getGaugeCell(_("Desktop Keys: ") + percent(object.desktopKeysAssigned, object.desktopKeysOrdered), _("${value} units used of ${maximum} units"), object.desktopKeysOrdered, object.desktopKeysAssigned);
                                    }},
                                {name: _("Desktop Quota"), field: "desktopQuotaOrdered", renderCell: function (object) {
                                        return getGaugeCell(_("Desktop Quota: ") + percent(object.desktopQuotaAssigned, object.desktopQuotaOrdered), _("${value} GB used of ${maximum} GB"), object.desktopQuotaOrdered, object.desktopQuotaAssigned);
                                    }}
                            ],
                            store: groupsStore
                        }
                    ];

                    // Check if partner canceled his subscription
                    var subscriptionStatus = mozyAccount.partnerStatus || "enabled";
                    var arrayContent = [ outputHeadline, ["aps/ListSwitcher", [["aps/ActiveList", activeItems], gridGroups]] ];

                    // Page Container - Adding Containers to the page
                    var pageContainer = ["aps/PageContainer", {id: "page"}, (subscriptionStatus === "disabled") ? [] : arrayContent];

                    // on Page Display
                    load(pageContainer).then(function () {
                        // Show a message if the partner has disabled the subscription
                        if (subscriptionStatus === "disabled") displayMessage(_("The partner has disabled the subscription"), "error");
                        // Add New Group Button - Click
                        registry.byId("newGroupButton").on("click", function () {
                            aps.apsc.gotoView("mozyProAccount.groups.new");
                        });
                    });
                });
            }
        });
