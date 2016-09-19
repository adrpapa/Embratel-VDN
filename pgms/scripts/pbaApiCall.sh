curl --connect-timeout 10 -d@'
<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
    <methodName>Execute</methodName>
    <params>
        <param>
            <value>
                <struct>
                    <member>
                        <name>Server</name>
                        <value><string>BM</string></value>
                    </member>
                    <member>
                        <name>Method</name>
                        <value><string>
                            SubscriptionResourcesListGet_API
                        </string></value>
                    </member>
                    <member>
                        <name>Params</name>
                        <value>
                            <array>
                                <data>
                                    <value><i4>1000001</i4></value>
                                    <value><i4>0</i4></value>
                                </data>
                            </array>
                        </value>
                    </member>
                </struct>
            </value>
        </param>
    </params>
</methodCall>
' -H 'Content-type:text/xml' https://painel.lab.embratelcloud.com.br:5224/RPC2


