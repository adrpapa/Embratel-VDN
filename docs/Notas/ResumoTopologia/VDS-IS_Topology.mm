<map version="freeplane 1.2.0">
<!--To view this file, download free mind mapping software Freeplane from http://freeplane.sourceforge.net -->
<node TEXT="VDS-IS" ID="ID_1723255651" CREATED="1283093380553" MODIFIED="1463852304274"><hook NAME="MapStyle">
    <properties show_note_icons="true"/>

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
<hook NAME="AutomaticEdgeColor" COUNTER="8"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Videoscape Delivery Suite - Internet Streamer
    </p>
  </body>
</html>

</richcontent>
<node TEXT="Management" POSITION="right" ID="ID_1264557541" CREATED="1463852644277" MODIFIED="1463858917427">
<edge COLOR="#7c0000"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Internet Streaming CDSM, a secure web browser-based user interface, is a centralized system management device that allows an administrator to manage and monitor the entire VDS-IS network. All devices, Service Engines and Service Routers, in the VDS-IS are registered to the Internet Streaming CDSM.
    </p>
    <p>
      
    </p>
    <p>
      Service Engines can be organized into user-defined device groups to allow administrators to apply configuration changes and perform other group operations on multiple devices simultaneously.
    </p>
    <p>
      One device may belong to multiple device groups.
    </p>
    <p>
      
    </p>
    <p>
      The Internet Streaming CDSM also provides an automated workflow to apply a software image upgrade to a device group.
    </p>
  </body>
</html>

</richcontent>
<node TEXT="CDSM&#xa;CDA=Internet Streaming Content&#xa;Delivery System Manager" ID="ID_910521096" CREATED="1463852359380" MODIFIED="1463853707952"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Applications(CDA)
    </p>
    <p>
      Content Delivery System Manager (CDSM) = Service Router (SR)
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
<node TEXT="Ingest" POSITION="right" ID="ID_1655073706" CREATED="1463852573483" MODIFIED="1463852579183">
<edge COLOR="#00ff00"/>
<node TEXT="CDSM - SE&#xa;CDA=Content Acquirer" ID="ID_1816415120" CREATED="1463852359380" MODIFIED="1463854102269"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Applications(CDA)
    </p>
    <p>
      Content Delivery System Manager (CDSM) = Service Engine (SE)
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
<node TEXT="Delivery" POSITION="right" ID="ID_1553148514" CREATED="1463852636716" MODIFIED="1463852641208">
<edge COLOR="#ffff00"/>
<node TEXT="CDE" ID="ID_1132614720" CREATED="1463852304236" MODIFIED="1463852586490" HGAP="10" VSHIFT="-10"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Engine
    </p>
  </body>
</html>
</richcontent>
<node TEXT="CDSM - SE&#xa;CDA=Internet Streamer" ID="ID_76108417" CREATED="1463852359380" MODIFIED="1463854020805"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Applications(CDA)
    </p>
    <p>
      Content Delivery System Manager (CDSM) = Service Engine (SE)
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
<node TEXT="Redirect client&#xa;requests for delivery" ID="ID_1935520816" CREATED="1463853387734" MODIFIED="1463853410910">
<node TEXT="CDSM - SR&#xa;CDA=Service Router" ID="ID_1840481181" CREATED="1463852359380" MODIFIED="1463853535122"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Applications(CDA)
    </p>
    <p>
      Content Delivery System Manager (CDSM) = Service Router (SR)
    </p>
  </body>
</html>

</richcontent>
<node TEXT="Distribution" ID="ID_289941273" CREATED="1463852595242" MODIFIED="1463854060730">
<node TEXT="CDSM - SE&#xa;CDA=Internet Streamer" ID="ID_1556458564" CREATED="1463852359380" MODIFIED="1463854091621"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content Delivery Applications(CDA)
    </p>
    <p>
      Content Delivery System Manager (CDSM) = Service Engine (SE)
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
</node>
</node>
</node>
</node>
</map>
