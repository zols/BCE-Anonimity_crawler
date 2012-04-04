/*jshint expr:true */
		var allFontsArray = '';
		var data = "", userId;

		$(document).ready(function() {
			// LocalStorage, cookie
			if (Modernizr.localstorage) {
				if (localStorage.getItem('userId') != undefined) {
					userId = localStorage.getItem('userId');
					data = "userId="+userId+"&";
				}
			} else {
				if ($.cookie("userId") != undefined) {
					userId = $.cookie('userId');
					data = "userId="+userId+"&";
				}
			}

			// Resolution
			data += "screenWidth="+screen.width+"&";
			data += "screenHeight="+screen.height+"&";

			// OS, browser
			var os = PluginDetect.OS, OS = "";
			switch (os) {
				case 1:
					OS = "Windows";
					break;
				case 2:
					OS = "Macintosh";
					break;
				case 3:
					OS = "Linux";
					break;
				case 21.1:
					OS = "iPhone";
					break;
				case 21.2:
					OS = "iPod";
					break;
				case 21.3:
					OS = "iPad";
					break;
				default:
					OS = "Other";
			}

			var browser, version;

			if (PluginDetect.isIE) {
				browser = "Internet Explorer";
				version = PluginDetect.verIE;
			}

			if (PluginDetect.isGecko) {
				browser = "Gecko";
				version = PluginDetect.verGecko;
			}

			if (PluginDetect.isSafari) {
				browser = "Safari";
				version = PluginDetect.verSafari;
			}

			if (PluginDetect.isChrome) {
				browser = "Chrome";
				version = PluginDetect.verChrome;
			}

			if (PluginDetect.isOpera) {
				browser = "Opera";
				version = PluginDetect.verOpera;
			}

			data += "browser="+browser+"&";
			data += "browserVersion="+version+"&";
			data += "os="+OS+"&";

			// Flash, Silverlight
			var statusSilverlight = PluginDetect.isMinVersion('Silverlight', '0');
			if (statusSilverlight > 0) {
				data += "Silverlight="+PluginDetect.getVersion('Silverlight')+"&";
			}

			var statusFlash = PluginDetect.isMinVersion('Flash', '0');
			if (statusFlash > 0) {
				data += "Flash="+PluginDetect.getVersion('Flash')+"&";
			}
			// Status
			// -0.2 : Plugin is installed but not enabled
			// -1	: Silverlight is out of date or not installed
			// -2	: ActiveX is disabled

			// Modernizr
			data += "applicationCache="+Modernizr.applicationcache+"&";	// Application Management
			data += "history="+Modernizr.history+"&";					// History Management
			data += "audio="+Modernizr.audio+"&";						// HTML5 audio
			data += "video="+Modernizr.video+"&";						// HTML5 video
			data += "indexedDB="+Modernizr.indexeddb+"&";				// IndexedDB
			data += "localStorage="+Modernizr.localstorage+"&";			// localStorage
			data += "sessionStorage="+Modernizr.sessionstorage+"&";		// sessionStorage
			data += "webSockets="+Modernizr.websockets+"&";				// webSockets
			data += "webSQLDatabase="+Modernizr.websqldatabase+"&";		// webSQLDatabase
			data += "webWorkers="+Modernizr.webworkers+"&";				// webWorkers
			data += "geoLocation="+Modernizr.geolocation+"&";			// geoLocation
			data += "touch="+Modernizr.touch+"&";						// touch
			data += "webGL="+Modernizr.webgl+"&";						// WebGL
			var connection = navigator.connection || {'type':'0'}; 
			data += "connectionType="+connection.type+"&";// Connection type

			// Referrer
			data += "referrer="+document.referrer+"&";

			// GeoLocation
			$.getJSON(
				'http://api.ipinfodb.com/v3/ip-city/?key=178043c468512dd9488a9b8e5c7d92528742bdbf6aa48df35fe57140007e35c4&format=json&callback=?',
				function(position) {
					data += "positionLatitude="+position.latitude+"&";
					data += "positionLongitude="+position.longitude+"&";

					// Embedding FontList Flash
					embedFontSWF();
				}
			);

			// Embed FontList SWF
			function embedFontSWF() {
				if (statusFlash > 0) {
					swfobject.embedSWF("public/swf/FontList.swf", "FontListSWF", "1", "1", "9.0.0");
					var isFlashRunning = function( run ){
						var flash = $( "#FontListSWF" )[0];
						if( flash && typeof flash.capture !== "undefined" ){
							console.log( "got you flash, nothing to do here" );
						}
						else if( run === 0 ){
							console.log( "still no flash calling sendData" );
							sendData();
						}
						else{
							console.log( "checking for flash..." );
							window.setTimeout( isFlashRunning, 1000 * ( 4 - run ), run - 1 );
    				}
					};

					isFlashRunning( 3 );
				} else {
					sendData();
				}				
			}
		});
function populateFontList(fontArr) {
	for (var key in fontArr) {
		var fontName = fontArr[key];

		allFontsArray += fontName.replace(/^\s\s*/, '').replace(/\s\s*$/, '') +'|';
	}
	data += "fonts="+allFontsArray+"&";
	sendData();
}

function sendData() {
	$.ajax({
		type: "POST",
		dataType: "json",
		url: baseUrl+"/ajax/save/index",
		data: data,
		success: function(data) {
			localStorage.setItem('userId', data.userId);
			$.cookie("userId", data.userId);

			localStorage.setItem('ID', data.ID);
			$.cookie("ID", data.ID);

			if (data.link != "") {
				$('#fbButtonPlaceholder').html('<a href="'+data.link+'" id="fbButton">Facebook adataim megosztása</a>');
			} else {
				$('#fbButtonPlaceholder').html('Adatait elmentettük');
			}

			console.log("User ID: "+data.userId);
		}
	});
}