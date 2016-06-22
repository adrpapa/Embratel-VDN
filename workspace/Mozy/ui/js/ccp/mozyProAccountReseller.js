/**
 *
 * @parent mozyProAccountReseller.html
 * @param {APS} load
 */
require(["aps/load", "dojo/store/Memory", "aps/xhr", "dojo/promise/all", "aps/Gauge", "dojo/when", "dojo/Deferred", "aps/ResourceStore", "dijit/registry", "aps/Message", "aps/ready!"],
        function (load, Memory, xhr, all, Gauge, when, Deferred, Store, registry, Message) {
            "use strict";
            
            /**
             * Returns aps/Output control, filled with parameter data
             * @param {string} label
             * @param {string} value
             * @returns {Array}
             */
            var getOutput = function (label, value) {
                var output =  ["aps/Output", { label: label, value: value }];
                return output;
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
            var getGaugeCell = function (title, legend, maximum, value) {
                var classesMap = {};
                    classesMap[maximum * 0.5] = "warn";
                    classesMap[maximum * 0.75] = "over";
                return new Gauge({title: title, legend: legend, minimum: 0, maximum: maximum, value: value, width: "65%", classesMap: classesMap});
            };

            /**
             * Returns the percentage between the value and maximum received from parameters
             * @param {type} value
             * @param {type} maximum
             * @returns {String}
             */
            var percent = function (value, maximum) {
                var percentage = 0;
                if (maximum !== 0) {
                    percentage = value * 100 / maximum;
                }
                return percentage.toFixed(2) + "%";
            };

            // Contexts
            var model = aps.context.vars.mozyProAccount;
            
            if(model.partnerId === null)
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
                            when(xhr("/aps/2/resources/" + model.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: resultA}}),
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
                                                    when(xhr("/aps/2/resources/" + model.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: emailA}}),
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
                                                                    when(xhr("/aps/2/resources/" + model.aps.id + "/createPartner", {method: "POST", handleAs: "text", query: {staffGUID: guid}}),
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
                // XHR to getLicense custom method
                xhr("/aps/2/resources/?implementing(" + model.aps.type + "),eq(partnerId," + model.partnerId +"),eq(accountType,CR)")
                    .then(function (customers) {
                        // Prepare Array of Request to the server
                        var arrCustomerUsers = [];
                        for (var i = 0; i < customers.length; i++) {
                            // Request to the server for each customer users
                            arrCustomerUsers.push(xhr("/aps/2/resources/" + customers[i].aps.id + "/mozyProAccountUser"));
                        }
                        all(arrCustomerUsers).then(function (arrayCustomersUsers) {

                            // Prepare Active Items for each customer
                            var arrActiveItems = [];
                            for (var i = 0; i < customers.length; i++) {
                                var customer = customers[i];

                                // Grid for each group with the list of users
                                var contUsersGrid = ["aps/Grid",
                                    {
                                        columns: [
                                            {name: _("Name"), field: "displayName", "filter": {"title": _("Name")}},
                                            {name: _("Email"), field: "login", "filter": {"title": _("Email")}},
                                            {name: _("Server Licenses"), field: "serverLicSum"},
                                            {name: _("Server Quota"), field: "serverQuotaSum"},
                                            {name: _("Desktop Licenses"), field: "desktopLicSum"},
                                            {name: _("Desktop Quota"), field: "desktopQuotaSum"}
                                        ],
                                        store: new Memory({data: arrayCustomersUsers[i]})
                                    }
                                ];

                                // Infoboard with gauges depending of keys ordered by type
                                var infoBoard = [];
                                if (customer.desktopLicenseNum.limit > 0) {
                                    infoBoard.push(getGauge(_("Desktop Quota: ${percent}"), _("${value} GB used of ${maximum} GB"), customer.desktopQuota.limit, customer.desktopQuota.usage || 0));
                                    infoBoard.push(getGauge(_("Desktop Keys: ${percent}"), _("${value} units used of ${maximum} units"), customer.desktopLicenseNum.limit, customer.desktopLicenseNum.usage || 0));
                                }
                                if (customer.serverLicenseNum.limit > 0) {
                                    infoBoard.push(getGauge(_("Server Quota: ${percent}"), _("${value} GB used of ${maximum} GB"), customer.serverQuota.limit, customer.serverQuota.usage || 0));
                                    infoBoard.push(getGauge(_("Server Keys: ${percent}"), _("${value} units used of ${maximum} units"), customer.serverLicenseNum.limit, customer.serverLicenseNum.usage || 0));
                                }

                                // ActiveIstem for the customer
                                var activeItemCustomer = ["aps/ActiveItem",
                                    {
                                        title: customer.companyName,
                                        collapsible: true,
                                        description: customer.userName
                                    }, [
                                        ["aps/InfoBoard", {cols: 4}, infoBoard],
                                        ["aps/Container", {visible:true}, [contUsersGrid]]
                                    ]
                                ];
                                arrActiveItems.push(activeItemCustomer);
                            }

                            // Prepare data for the grid
                            var customersMemory = new Memory({data: customers});
                            // First container - User credentials
                            var fieldSet = ["aps/FieldSet", {title: _("Reseller Info")},
                                [
                                    getOutput(_("Username"), model.userFullName),
                                    getOutput(_("Login"), model.userName)
                                ]
                            ];

                            // Second container - Grid with data retrieved from XHR
                            var grid = ["aps/Grid", {
                                    id: "userGrid",
                                    columns: [
                                        {name: _("Username"), field: "companyName", "filter": {"title": _("Username")}},
                                        {name: _("Server Licenses"), field: "serverLicenseNum.limit", renderCell: function (object) {
                                                return getGaugeCell(_("Server Keys: ") + percent(object.serverLicenseNum.usage || 0, object.serverLicenseNum.limit), _("${value} units used of ${maximum} units"), object.serverLicenseNum.limit, object.serverLicenseNum.usage || 0);
                                            }},
                                        {name: _("Server Quota"), field: "serverQuotaOrdered", renderCell: function (object) {
                                                return getGaugeCell(_("Server Quota: ") + percent(object.serverQuota.usage || 0, object.serverQuota.limit), _("${value} GB used of ${maximum} GB"), object.serverQuota.limit, object.serverQuota.usage || 0);
                                            }},
                                        {name: _("Desktop Licenses"), field: "desktopKeysOrdered", renderCell: function (object) {
                                                return getGaugeCell(_("Desktop Keys: ") + percent(object.desktopLicenseNum.usage || 0, object.desktopLicenseNum.limit), _("${value} units used of ${maximum} units"), object.desktopLicenseNum.limit, object.desktopLicenseNum.usage || 0);
                                            }},
                                        {name: _("Desktop Quota"), field: "desktopQuotaOrdered", renderCell: function (object) {
                                                return getGaugeCell(_("Desktop Quota: ") + percent(object.desktopQuota.usage || 0, object.desktopQuota.limit), _("${value} GB used of ${maximum} GB"), object.desktopQuota.limit, object.desktopQuota.usage || 0);
                                            }}
                                        ],
                                        store: customersMemory
                                    }];
                            // 
                            var gridContainer = ["aps/Container", {title: _("Reseller customers")}, [["aps/ListSwitcher", [["aps/ActiveList", arrActiveItems], grid]] ]];

                            // Page Container - Adding containers to the page
                            var pageContainer = ["aps/PageContainer", [fieldSet, gridContainer ] ];

                            // on Page Display
                            load(pageContainer);
                        });
                    });
            }

            
        });
