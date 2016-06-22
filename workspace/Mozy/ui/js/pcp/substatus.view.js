require([
    "aps/ResourceStore",
    "dojo/when",
    "dijit/registry",
    "aps/load",
    "aps/Memory",
    "aps/Output",
    "dojo/Deferred",
    "dojox/mvc/getStateful",
    "aps/ready!"
], function(Store, when, registry, load, Memory, Output, Deferred, getStateful) {
    
    // The globals model and the tenant link
    var model = getStateful(aps.context.vars.globals);
    var store = new Store({
        target : "/aps/2/resources/" + model.aps.id + "/mozyProAccount",
        baseQuery: "select(subscription),eq(partnerId,null()),ne(subscription.subscriptionId,null())"
    });

    var getStore = function(){
        var deferred = new Deferred();
        store.query().then(function(store){
            deferred.resolve(store);
        }).otherwise( function(error){
            deferred.cancel(error);
        });
        return deferred.promise;
    };

    var handleEvents = function() {
        var grid = registry.byId("plansgrid");

        var rowCount = registry.byId("plansgrid").get("_totalItemCount");
        if (rowCount < 10) {
            grid.set("showPaging", false);
        }

    };

    when(getStore()).then( function(str) {
        var storeGrid = new Memory({data: str, idProperty: "aps.id"});

        var subsStore = new Store({
            target: "/aps/2/resources/"
        });
        var pageContainer = ["aps/PageContainer", {
            id: "page"
        },
            [
                ["aps/FieldSet", { showLabels: false },
                    [[ "aps/Output", {
                        label: "",
                        escapeHTML: false,
                        value: _("These are the subscriptions that have been provisioned in OSA, but are not being provisioned in Mozy.")
                    }]]
                ],
                ["aps/Grid", {
                    id: "plansgrid",
                    style: "margin-top:30px;",
                    columns: [{
                        field: "subscription.name",
                        name: "Name"
                    },
                    {
                        name: "Subscription Id",
                        renderCell: function(object){
                            if(object.subscription)
                            {
                                return new Output({
                                escapeHtml: false,
                                innerHTML: "<a href='javascript:void(0)'>" + object.subscription.subscriptionId + "</a>",
                                type: "link",
                                onClick: function(){
                                    when(subsStore.query('implementing(http://parallels.com/aps/types/pa/subscription/1.0),subscriptionId=' + object.subscription.subscriptionId), function(subscription){
                                        aps.apsc.gotoView("http://parallels.com/aps/types/pa/poa/1.0#subscription", subscription[0].aps.id);
                                    });
                                }
                                }); }
                        }
                    }
                    ],
                    store: storeGrid
                }
                ]
            ]
        ];
        when(load(pageContainer), handleEvents);
    });
});
