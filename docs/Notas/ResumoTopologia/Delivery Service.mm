<map version="freeplane 1.2.0">
<!--To view this file, download free mind mapping software Freeplane from http://freeplane.sourceforge.net -->
<node TEXT="Delivery Service" ID="ID_1723255651" CREATED="1283093380553" MODIFIED="1463854844616"><hook NAME="MapStyle">
    <properties show_icon_for_attributes="true" show_note_icons="true"/>

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
<hook NAME="AutomaticEdgeColor" COUNTER="4"/>
<node TEXT="Ingest" POSITION="right" ID="ID_1655073706" CREATED="1463852573483" MODIFIED="1463956807717">
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
<node TEXT="Ingest Method" ID="ID_1381409364" CREATED="1463856972124" MODIFIED="1463856981725">
<node TEXT="Prefetch / content pinning" ID="ID_1196401733" CREATED="1463856983631" MODIFIED="1463857438709"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Content Acquirer receives metadata from the back-office in the form of an XML-formatted Manifest file, and using the information in the file, pulls the content into storage on the Content Acquirer.
    </p>
    <p>
      The content can be ingested by using different protocols.
    </p>
    <p>
      The supported protocols are FTP, HTTP, HTTPS, and CIFS, which are files copied to the Service Engine.
    </p>
    <p>
      The ingested content is then distributed to all Service Engines in the content Delivery Service.
    </p>
    <p>
      The content is stored on each Service Engine&#8217;s hard disk for a configurable amount of time or until the content entry gets deleted from the Manifest file.
    </p>
    <p>
      This is called content pinning.
    </p>
    <p>
      The Manifest file can be used to specify different policies for content ingest and also for streaming the prefetched content.
    </p>
    <p>
      For example, the policy could include specifying the expiry of the content, setting time windows in which the content is made available to users, and so on.
    </p>
    <p>
      Note The content type (MIME) value cannot exceed 32 characters.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Dynamic" ID="ID_1811966753" CREATED="1463856990880" MODIFIED="1463857858772"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content can be dynamically ingested into the VDS-IS.
    </p>
    <p>
      Dynamic ingest is triggered when a Service Engine&#8217;s Internet Streamer application does not find a client&#8217;s requested content in its local hard disk storage.
    </p>
    <p>
      All Service Engines participating in the content Delivery Service coordinate to form a content distribution tunnel starting at the origin server and ending at the Service Engine responding to the client request.
    </p>
    <p>
      As the content flows through this tunnel, the participating Service Engines cache a copy of the content.
    </p>
    <p>
      Subsequent requests for the same content are served off the VDS-IS network.
    </p>
    <p>
      <b>Content ingested and distributed by this method is deleted if clients do not request it frequently. </b>
    </p>
    <p>
      
    </p>
    <p>
      The Internet Streaming CDSM manages this ingest method internally, not by instructions embedded in a Manifest file, and manages the storage automatically.
    </p>
    <p>
      The Internet Streaming CDSM also provides the ability to purge any dynamically ingested content out of the Service Engines.
    </p>
    <p>
      <b>Content is identified by a URL, which is also used to delete the content.</b>
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Hybrid" ID="ID_1438108632" CREATED="1463856998567" MODIFIED="1463857969437"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The hybrid ingest method provides a very powerful solution by combining the features of the prefetch ingest and the dynamic ingest methods.
    </p>
    <p>
      The metadata and control information about the content, defined in the Manifest file, is propagated and pinned to all Service Engines participating in the content Delivery Service.
    </p>
    <p>
      However, the content is not prefetched. Ingest occurs upon user request for the content.
    </p>
    <p>
      Content that is cached on the Service Engines by using this method is subject to the same deletion rules as the dynamic ingest method.
    </p>
    <p>
      The metadata that is propagated can be used to specify explicit controls and policies for streaming the content.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Live Stream Ingest and Split" ID="ID_495823188" CREATED="1463857014000" MODIFIED="1463858158810"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The live stream ingest method distributes a live content feed to all of the Service Engines participating in the content Delivery Service and helps to scale the content delivery to a very large audience. This method leverages the live stream splitting capabilities of the Internet Streamer application and optimizes the access by doing a one-to-many split to all Service Engines in the content Delivery Service. The Internet Streaming CDSM provides the necessary interface to schedule the streaming of live programs. Advanced techniques are used to enhance the performance of live streaming.
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
</node>
<node TEXT="Parameters" POSITION="right" ID="ID_944767034" CREATED="1463855075297" MODIFIED="1463859585649">
<edge COLOR="#00ff00"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      <font color="#000000" face="monospace">Origin server </font>
    </p>
    <p>
      <font face="monospace">Service routing domain name</font>
    </p>
    <p>
      <font face="monospace">Service Engines participating in the Delivery Service<br/>Service Engine designated as the Content Acquirer </font>
    </p>
    <p>
      <font face="monospace">QoS value for content ingest </font>
    </p>
    <p>
      <font face="monospace">QoS value for multicast data </font>
    </p>
    <p>
      <font face="monospace"><br/>
      </font>
    </p>
  </body>
</html>
</richcontent>
<node TEXT="" ID="ID_1552096404" CREATED="1463856176522" MODIFIED="1463856247503">
<hook URI="Delivery%20Service_3404226102046708714.png" SIZE="0.872093" NAME="ExternalObject"/>
</node>
</node>
<node TEXT="Delivery" POSITION="right" ID="ID_1553148514" CREATED="1463852636716" MODIFIED="1463858436834">
<edge COLOR="#ffff00"/>
<richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Service Router handles client requests for content and determines the best Service Engine to deliver it based on proximity, load and health states.
    </p>
    <p>
      Once the best Service Engine has been determined, the content is delivered to the client device by means of one of the following mechanisms:
    </p>
    <p>
      &#160;&#160;&#160;&#160;Static Content Download using HTTP &#8212;Content is downloaded by the client device before it can be rendered to the user.
    </p>
    <p>
      &#160;&#160;&#160;&#160;Progressive Content Download using HTTP &#8212;Content is rendered in segments to the user before it has been fully downloaded.
    </p>
    <p>
      &#160;&#160;&#160;&#160;Content Streaming using HTTP, RTMP, RTSP, or RTP &#8212;Content is streamed to the client device, Service Engines collect feedback and can fine-tune streaming. Advanced error recovery can also be performed. This is a very common method of streaming video content to client devices.
    </p>
  </body>
</html>
</richcontent>
<node TEXT="Redirect client&#xa;requests for delivery" ID="ID_1935520816" CREATED="1463853387734" MODIFIED="1463853410910">
<node TEXT="CDSM - SR&#xa;CDA=Service Router" ID="ID_1840481181" CREATED="1463852359380" MODIFIED="1463860813565"><richcontent TYPE="NOTE">

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
    <p>
      
    </p>
    <p>
      The Service Router can be configured as both the Request Routing Engine and the Proximity Engine, or the Service Router can be configured only as the Request Routing Engine.
    </p>
    <p>
      Additionally, the Service Router can act as a standalone Proximity Engine by not configuring the Request Routing Engine as the authoritative DNS server.
    </p>
  </body>
</html>
</richcontent>
<node TEXT="" ID="ID_831955709" CREATED="1463860683347" MODIFIED="1463860683347"/>
<node TEXT="Request Routing Engine" ID="ID_1262851882" CREATED="1463860685461" MODIFIED="1463860873572"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Request Routing Engine mediates requests from the client devices and redirects the requests to the most appropriate Service Engine. It monitors the load of the devices and does automatic load balancing.
    </p>
    <p>
      
    </p>
    <p>
      The Request Routing Engine is the authoritative Domain Name System (DNS) server for the routed request for the fully qualified domain name (FQDN) of the origin server. In other words, the Request Routing Engine responds to any DNS queries for that domain.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Proximity Engine" ID="ID_882522188" CREATED="1463860701620" MODIFIED="1463860708000"/>
</node>
</node>
<node TEXT="Distribution" ID="ID_289941273" CREATED="1463852595242" MODIFIED="1463855434485">
<node TEXT="CDSM - SE&#xa;CDA=Internet Streamer" ID="ID_1556458564" CREATED="1463852359380" MODIFIED="1463859793503"><richcontent TYPE="NOTE">

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
    <p>
      
    </p>
    <p>
      All Internet Streamers participating in a Delivery Service pull the metadata from a peer Internet Streamer called a forwarder , which is selected by the internal routing module. Each Internet Streamer participating in a Delivery Service has a forwarder Internet Streamer. The Content Acquirer is the top-most forwarder in the distribution hierarchy. In the case of prefetched ingest, each Internet Streamer in the Delivery Service looks up the metadata record and fetches the content from its forwarder. For live or cached content metadata, only the metadata is distributed.
    </p>
    <p>
      
    </p>
    <p>
      The content associated with the metadata for live and cached content is fetched by the specified protocol engine, which uses the dynamic ingest mechanism. When a request for a non-prefetched content arrives at an Internet Streamer, the protocol engine application gets the information about the set of upstream Internet Streamers through which the content can be acquired. In the case of dynamic ingest, the Internet Streamer uses the cache routing function to organize itself as a hierarchy of caching proxies and performs a native protocol cache fill. Live stream splitting is used to organize the Internet Streamers into a live streaming hierarchy to split a single incoming live stream to multiple clients. The live stream can originate from external servers or from ingested content. Windows Media Engine, Movie Streamer Engine, and Flash Media Streaming engine support live stream splitting.
    </p>
  </body>
</html>
</richcontent>
<node TEXT="Static Content Download using HTTP" ID="ID_1901390705" CREATED="1463858438770" MODIFIED="1463858473434"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content is downloaded by the client device before it can be rendered to the user.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT=" Progressive Content Download using HTTP" ID="ID_1945328460" CREATED="1463858478571" MODIFIED="1463858533446"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content is rendered in segments to the user before it has been fully downloaded.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Content Streaming using HTTP, RTMP, RTSP, or RTP" ID="ID_352906748" CREATED="1463858543317" MODIFIED="1463858653467"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      Content is streamed to the client device, Service Engines collect feedback and can fine-tune streaming.
    </p>
    <p>
      Advanced error recovery can also be performed.
    </p>
    <p>
      This is a very common method of streaming video content to client devices.
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
<node TEXT="Protocol Engine Applications" ID="ID_117567372" CREATED="1463859874708" MODIFIED="1463859895802">
<node TEXT="" ID="ID_440500950" CREATED="1463859897564" MODIFIED="1463859897564"/>
<node TEXT="Web Engine" ID="ID_309143473" CREATED="1463859913701" MODIFIED="1463860393538"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      All HTTP client requests that are redirected to a Service Engine by the Service Router are handled by the Web Engine.
    </p>
    <p>
      On receiving the request, the Web Engine uses its best judgment and either handles the request or forwards it to another component within the Service Engine.
    </p>
    <p>
      Service rules can be configured that dictate how the Web Engine responds when client requests match specific patterns. The patterns can be a domain or host name, certain header information, the request source IP address, or a Uniform Resource Identifier (URI). Some of the possible responding actions are to allow or block the request, generate or validate the URL signature, or rewrite or redirect the URL.
    </p>
    <p>
      The Web Engine, using HTTP, can serve the request from locally stored content in the VDS-IS or from any upstream proxy or origin server.
    </p>
    <p>
      On receiving an HTTP request for content, the Web Engine decides whether the content needs to be streamed by the Windows Media Engine, and if so, hands the request over to the Windows Media Engine, otherwise the request is handled by the Web Engine.
    </p>
    <p>
      The message size between Web Engine and Windows Media Streaming is 12 KB.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Windows Media Streaming Engine" ID="ID_1404963930" CREATED="1463859921565" MODIFIED="1463860422830"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Windows Media Streaming engine uses Windows Media Technology (WMT), a set of streaming solutions for creating, distributing, and playing back digital media files across the Internet. WMT includes the following applications:
    </p>
    <p>
      &#160;&#160;&#160;&#160;Windows Media Player&#8212;End-user application
    </p>
    <p>
      &#160;&#160;&#160;&#160;Windows Media Server&#8212;Server and distribution application
    </p>
    <p>
      &#160;&#160;&#160;&#160;Windows Media Encoder&#8212;Encodes media files for distribution
    </p>
    <p>
      &#160;&#160;&#160;&#160;Windows Media Codec&#8212;Compression algorithm applied to live and on-demand content
    </p>
    <p>
      &#160;&#160;&#160;&#160;Windows Media Rights Manager (WMRM)&#8212;Encrypts content and manages user privileges
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Movie Streamer Engine" ID="ID_213090435" CREATED="1463859937310" MODIFIED="1463860573549"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Movie Streamer Engine is an open-source, standards-based, streaming server that delivers hinted MPEG-4, hinted 3GP, and hinted MOV files to clients over the Internet and mobile networks using the industry-standard RTP and RTSP. Hinted files contain hint tracks, which store packetization information that tell the streaming server how to package content for streaming.
    </p>
    <p>
      
    </p>
    <p>
      The Movie Streamer Engine is an RTSP streaming engine that supports Third Generation Partnership Project (3GPP) streaming files (.3gp). Support of 3GPP provides for the rich multimedia content over broadband mobile networks to multimedia-enabled cellular phones.
    </p>
  </body>
</html>
</richcontent>
</node>
<node TEXT="Flash Media Streaming Engine" ID="ID_1992690170" CREATED="1463859948062" MODIFIED="1463860661914"><richcontent TYPE="NOTE">

<html>
  <head>
    
  </head>
  <body>
    <p>
      The Flash Media Streaming engine incorporates the Adobe Flash Media Server technology into the VDS-IS platform. The Flash Media Streaming engine is capable of hosting Flash Media Server applications that are developed using ActionScripts, such as VOD (prefetched content, or dynamic or hybrid ingested content), live streaming, and interactive applications.
    </p>
  </body>
</html>
</richcontent>
</node>
</node>
</node>
</node>
</node>
</map>
