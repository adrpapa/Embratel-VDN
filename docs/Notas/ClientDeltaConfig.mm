<map version="freeplane 1.2.0">
<!--To view this file, download free mind mapping software Freeplane from http://freeplane.sourceforge.net -->
<node TEXT="Client Delta Config" ID="ID_1723255651" CREATED="1283093380553" MODIFIED="1465585718763"><hook NAME="MapStyle">

<map_styles>
<stylenode LOCALIZED_TEXT="styles.root_node">
<stylenode LOCALIZED_TEXT="styles.predefined" POSITION="right">
<stylenode LOCALIZED_TEXT="default" MAX_WIDTH="600" COLOR="#000000" STYLE="as_parent">
<font NAME="SansSerif" SIZE="10" BOLD="false" ITALIC="false"/>
</stylenode>
<stylenode LOCALIZED_TEXT="defaultstyle.details"/>
<stylenode LOCALIZED_TEXT="defaultstyle.note"/>
<stylenode LOCALIZED_TEXT="defaultstyle.floating">
<edge STYLE="hide_edge"/>
<cloud COLOR="#f0f0f0" SHAPE="ROUND_RECT"/>
</stylenode>
</stylenode>
<stylenode LOCALIZED_TEXT="styles.user-defined" POSITION="right">
<stylenode LOCALIZED_TEXT="styles.topic" COLOR="#18898b" STYLE="fork">
<font NAME="Liberation Sans" SIZE="10" BOLD="true"/>
</stylenode>
<stylenode LOCALIZED_TEXT="styles.subtopic" COLOR="#cc3300" STYLE="fork">
<font NAME="Liberation Sans" SIZE="10" BOLD="true"/>
</stylenode>
<stylenode LOCALIZED_TEXT="styles.subsubtopic" COLOR="#669900">
<font NAME="Liberation Sans" SIZE="10" BOLD="true"/>
</stylenode>
<stylenode LOCALIZED_TEXT="styles.important">
<icon BUILTIN="yes"/>
</stylenode>
</stylenode>
<stylenode LOCALIZED_TEXT="styles.AutomaticLayout" POSITION="right">
<stylenode LOCALIZED_TEXT="AutomaticLayout.level.root" COLOR="#000000">
<font SIZE="18"/>
</stylenode>
<stylenode LOCALIZED_TEXT="AutomaticLayout.level,1" COLOR="#0033ff">
<font SIZE="16"/>
</stylenode>
<stylenode LOCALIZED_TEXT="AutomaticLayout.level,2" COLOR="#00b439">
<font SIZE="14"/>
</stylenode>
<stylenode LOCALIZED_TEXT="AutomaticLayout.level,3" COLOR="#990000">
<font SIZE="12"/>
</stylenode>
<stylenode LOCALIZED_TEXT="AutomaticLayout.level,4" COLOR="#111111">
<font SIZE="10"/>
</stylenode>
</stylenode>
</stylenode>
</map_styles>
</hook>
<hook NAME="AutomaticEdgeColor" COUNTER="2"/>
<node TEXT="live" POSITION="right" ID="ID_1399273044" CREATED="1465585225584" MODIFIED="1465585843334" VSHIFT="-30">
<edge COLOR="#ff0000"/>
<node TEXT="event input filter" ID="ID_1332222383" CREATED="1465585247370" MODIFIED="1465585485893">
<node TEXT="label" ID="ID_1596424707" CREATED="1465589104345" MODIFIED="1465589146236" HGAP="30" VSHIFT="-10">
<node TEXT="event name" ID="ID_882037977" CREATED="1465589126371" MODIFIED="1465589133467"/>
</node>
<node TEXT="udp port" ID="ID_1528175854" CREATED="1465585269023" MODIFIED="1465585857709" HGAP="30" VSHIFT="-10">
<node TEXT="next available" ID="ID_1735428595" CREATED="1465585464918" MODIFIED="1465585469516"/>
</node>
<node TEXT="storage location" ID="ID_390071301" CREATED="1465585275064" MODIFIED="1465585285735">
<node TEXT="&lt;live_root&gt;/clientID/eventName" ID="ID_1826701510" CREATED="1465585422839" MODIFIED="1465589197649"/>
</node>
<node TEXT="client live output template" ID="ID_1139013951" CREATED="1465585294960" MODIFIED="1465585861608" HGAP="30" VSHIFT="10">
<node TEXT="output filters" ID="ID_766170357" CREATED="1465585324665" MODIFIED="1465585336117">
<node TEXT="custom url" ID="ID_13730385" CREATED="1465585340535" MODIFIED="1465585394947">
<node TEXT="clientID/live/$fn$ (storage location)" ID="ID_1375469508" CREATED="1465585396991" MODIFIED="1465585678355"/>
</node>
</node>
</node>
</node>
</node>
<node TEXT="vod" POSITION="right" ID="ID_588462143" CREATED="1465585239344" MODIFIED="1465585838115" VSHIFT="20">
<edge COLOR="#0000ff"/>
<node TEXT="client vod input filter" ID="ID_1965782820" CREATED="1465585495430" MODIFIED="1465585521487">
<node TEXT="client watch folder" ID="ID_1523635962" CREATED="1465585521492" MODIFIED="1465585889420" VSHIFT="-10">
<node TEXT="&lt;wfroot&gt;/clientID/" ID="ID_92287556" CREATED="1465585532691" MODIFIED="1465585573097"/>
</node>
<node TEXT="client vod output template" ID="ID_37470286" CREATED="1465585588102" MODIFIED="1465585872175" VSHIFT="10">
<node TEXT="output filters" ID="ID_1725672760" CREATED="1465585601475" MODIFIED="1465585607998">
<node TEXT="custom url" ID="ID_1625400219" CREATED="1465585608005" MODIFIED="1465585619574">
<node TEXT="clientID/vod/$fn$ (filename)" ID="ID_1960913512" CREATED="1465585619580" MODIFIED="1465585656083"/>
</node>
</node>
</node>
</node>
</node>
</node>
</map>
