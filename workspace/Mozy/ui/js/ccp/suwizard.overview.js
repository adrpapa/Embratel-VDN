require(["aps/load", "aps/WizardData", "aps/ready!"],
        function(load, WizardData) {
                "use strict";

                // We define the model and the wizard
                var ModelMozy = aps.context.params.objects[0];

                //Save data for recovery
                WizardData.put(ModelMozy);

                // Show the information
                load(["aps/PageContainer",
                        [["aps/FieldSet", { title: _("User information"), showLabels: true },
                                [
                                        ["aps/Output", { id: "displayName", label: _("Display Name "), value: ModelMozy.displayName }],
                                        ["aps/Output", { id: "login", label: _("Login "), value: ModelMozy.login }],
                                        ["aps/Output", { id: "password", label: _("Password "), value: "*****" }],
                                        ["aps/Output", { id: "user_group_id", label: _("Group "), value: ModelMozy.user_group_id }]
                                ]
                        ],
                        ["aps/FieldSet", { title: _("License"), showLabels: true },
                                [
                                        ["aps/Output", { id: "type", label: _("Type "), value: (ModelMozy.desktopQuotaSum !== 0 ? 'Desktop' : 'Server') }],
                                        ["aps/Output", { id: "quota", label: _("Quota "), value: (ModelMozy.desktopQuotaSum !== 0 ? ModelMozy.desktopQuotaSum : ModelMozy.serverQuotaSum) }]
                                ]
                        ]]
                ]);
        }
);