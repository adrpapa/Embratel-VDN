<map version="freeplane 1.2.0">
<!--To view this file, download free mind mapping software Freeplane from http://freeplane.sourceforge.net -->
<node TEXT="VDS-IS Legacy APIs" ID="ID_1723255651" CREATED="1283093380553" MODIFIED="1463870888484"><hook NAME="MapStyle">
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
<hook NAME="AutomaticEdgeColor" COUNTER="1"/>
<node TEXT="Replication Status APIs" POSITION="right" ID="ID_271191373" CREATED="1463870901832" MODIFIED="1463872207170">
<edge COLOR="#ff0000"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Returns a list of Delivery Services, Service Engines, or contents, and
    </p>
    <p>
      for each delivery service, an indication whether replication of content
    </p>
    <p>
      for the specified delivery service is complete or not.
    </p>
    <p>
      
    </p>
    <p>
      The Replication Status API performs one or more of the following tasks when executed:
    </p>
    <p>
      &#8226; Obtains the replication status of content on specified delivery services
    </p>
    <p>
      &#8226; Obtains the replication status of content for all Service Engines assigned to the specified delivery service
    </p>
    <p>
      &#8226; Obtains the replication status of content for all delivery services assigned to the specified Service Engine
    </p>
    <p>
      &#8226; Lists all replicated items of a specified Service Engine on a specified delivery service, with or without search criteria
    </p>
    <p>
      &#8226; Lists all nonreplicated items of a specified Service Engine on a specified delivery service, with or without search criteria
    </p>
    <p>
      &#8226; Lists all content items of a Service Engine on a specified delivery service, with or without search criteria
    </p>
    <p>
      
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Provisioning APIs" POSITION="right" ID="ID_882128503" CREATED="1463871190135" MODIFIED="1463871226117">
<edge COLOR="#ff0000"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Provides the CDSM1 with VDS-IS Delivery Service, location, and
    </p>
    <p>
      Service Engine information.
    </p>
  </body>
</html>

</richcontent>
<node TEXT="DeliveryService Provisioning" ID="ID_1156692339" CREATED="1463870912567" MODIFIED="1463871247004"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Delivery Service Provisioning API&#8212;Monitors and modifies
    </p>
    <p>
      VDS-IS network Delivery Services.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Location Provisioning" ID="ID_1705882319" CREATED="1463870929024" MODIFIED="1463871267093"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Location Provisioning API&#8212;Creates, modifies, or deletes a
    </p>
    <p>
      VDS-IS network location object.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Service Engine Provisioning" ID="ID_1483286677" CREATED="1463870937248" MODIFIED="1463871284072"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Service Engine Provisioning API&#8212;Activates, locates, or deletes a
    </p>
    <p>
      specified Service Engine.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Program" ID="ID_311002177" CREATED="1463870952384" MODIFIED="1463871326850"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Program API&#8212;Creates, modifies, validates, or deletes programs,
    </p>
    <p>
      and assigns or unassigns Service Engines and delivery services to
    </p>
    <p>
      programs.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Media for Program" ID="ID_1898377258" CREATED="1463870956416" MODIFIED="1463871349339"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Media API&#8212;Adds or deletes a media file from a Movie Streamer
    </p>
    <p>
      rebroadcast program, and updates the media file list for a Movie
    </p>
    <p>
      Streamer rebroadcast program.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="URL Management" ID="ID_608096680" CREATED="1463870964961" MODIFIED="1463871367030"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      &#160;URL Management API&#8212;Deletes single or multiple content objects.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Storage Priority Class API" ID="ID_297983507" CREATED="1463871390269" MODIFIED="1463871427045"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Storage Priority Class API&#8212;Creates, modifies, and deletes a
    </p>
    <p>
      cache storage priority class used for delivery services.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Multicast API" ID="ID_796017134" CREATED="1463871436790" MODIFIED="1463871451821"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Multicast API&#8212;Creates, modifies, and deletes multicast clouds,
    </p>
    <p>
      assigns and unassigns receiver SEs, and assigns and unassigns
    </p>
    <p>
      multicast cloud to delivery services.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="External System API" ID="ID_1754532513" CREATED="1463871451815" MODIFIED="1463871487060"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      &#160;External System API&#8212;Creates, modifies, and deletes external
    </p>
    <p>
      system configurations for forwarding SNMP traps from the
    </p>
    <p>
      CDSM.
    </p>
  </body>
</html>

</richcontent>
</node>
</node>
<node TEXT="Listing" POSITION="right" ID="ID_1961202748" CREATED="1463870973458" MODIFIED="1463872719831">
<edge COLOR="#00007c"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Listing API performs one or more of the following tasks when executed:
    </p>
    <p>
      &#8226; Lists selected content origin names or lists every content origin
    </p>
    <p>
      &#8226; Lists selected delivery service names and related content origin IDs or lists every delivery service
    </p>
    <p>
      &#8226; Lists selected Service Engine names or lists every Service Engine
    </p>
    <p>
      &#8226; Lists the location of the specified Service Engines
    </p>
    <p>
      &#8226; Lists selected cluster names or lists every cluster (cluster is the same thing as Service Engine)
    </p>
    <p>
      &#8226; Lists selected device group names or lists every device group
    </p>
    <p>
      &#8226; Lists the status of a device or device group
    </p>
    <p>
      &#8226; Lists an object, based on its string ID
    </p>
    <p>
      &#8226; Lists an object, based on its name
    </p>
    <p>
      &#8226; Lists all programs specified
    </p>
    <p>
      &#8226; Lists all multicast addresses currently in use by programs
    </p>
    <p>
      &#8226; Lists all multicast addresses currently in use
    </p>
    <p>
      &#8226; Lists the multicast address range reserved for programs
    </p>
    <p>
      &#8226; Lists all the multicast clouds.
    </p>
    <p>
      &#8226; Lists the cache storage priority classes
    </p>
    <p>
      &#8226; Lists the external systems configured
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Monitoring Statistics" POSITION="right" ID="ID_730659777" CREATED="1463870978625" MODIFIED="1463871547072">
<edge COLOR="#007c00"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Obtains monitoring statistics data about a single Service Engine or all
    </p>
    <p>
      the Service Engines in the VDS-IS network.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Streaming Statistics" POSITION="right" ID="ID_1384135513" CREATED="1463871005779" MODIFIED="1463871658270">
<edge COLOR="#7c007c"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Reports WMT2&#160;&#160;(Windows Media Technology), HTTP, Movie Streamer, and Flash Media data
    </p>
    <p>
      collected from the Service Engines or device groups and sends this
    </p>
    <p>
      data to the CDSM. Data obtained with the Streaming Statistics API can
    </p>
    <p>
      be saved and a customized report generated.
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="File Management APIs" POSITION="right" ID="ID_44797561" CREATED="1463871018474" MODIFIED="1463871697706">
<edge COLOR="#007c7c"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Performs file management functions on the external XML files used to
    </p>
    <p>
      configure the VDS-IS, and applies Coverage Zone and CDN Selector
    </p>
    <p>
      files to SRs3.
    </p>
  </body>
</html>

</richcontent>
<node TEXT="File Management API" ID="ID_1357458582" CREATED="1463871697701" MODIFIED="1463873019845"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The File Management API performs one or more of the following tasks when executed:
    </p>
    <p>
      &#8226; Displays a list of all the file types that can be registered with the CDSM
    </p>
    <p>
      &#8226; Registers an external file with the CDSM by either uploading a file from any location that is accessible from your PC or by importing a file from an external server
    </p>
    <p>
      &#8226; Validates a file before or after registering it with the CDSM
    </p>
    <p>
      &#8226; Modifies the metadata associated with a registered file
    </p>
    <p>
      &#8226; Immediately refetches a registered file from an external server
    </p>
    <p>
      &#8226; Deletes a registered file from the CDSM
    </p>
    <p>
      &#8226; Lists the details of a specific file or lists all files of a specific file type
    </p>
    <p>
      &#8226; Assigns a Coverage Zone file to an SR or unassigns a Coverage Zone file from an SR
    </p>
    <p>
      &#8226; Associates a CDN Selector file with an SR or disassociates a CDN Selector file from an SR
    </p>
  </body>
</html>

</richcontent>
</node>
<node TEXT="Certificate and Key File Management" ID="ID_1223036563" CREATED="1463871037196" MODIFIED="1463873630508"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Certificate and Key File Management API&#8212;Manages the HTTP
    </p>
    <p>
      Streaming Certificate and Key file
    </p>
    <p>
      
    </p>
    <p>
      The Certificate and Key File Management API performs one or more of the following tasks when executed:
    </p>
    <p>
      &#8226; Registers the Certificate and Key file for HTTPS Streaming
    </p>
    <p>
      &#8226; Modifies (updates) the Certificate and Key file for HTTPS Streaming
    </p>
    <p>
      &#8226; Deletes the Certificate and Key file for HTTPS Streaming
    </p>
    <p>
      &#8226; Lists the details of the Certificate and Key file for HTTPS Streaming
    </p>
    <p>
      
    </p>
  </body>
</html>

</richcontent>
</node>
</node>
</node>
</map>
