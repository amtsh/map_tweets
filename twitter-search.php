// Twitter Map with Backbone.js

$(function() {
 
//-- Data ---------------------------------------------------------------------//    
    var geocoder;
    geocoder = new google.maps.Geocoder();
    var Tweet = Backbone.Model;
    //Start the saerching once the user clicks search button
$('search_button').click(function(){
   
var customURL= "https://api.twitter.com/1.1/search/tweets.json?q=search_term_here&result_type=recent&include_entities=true&count=10&result_type=mixed" ;

    var Tweets = Backbone.Collection.extend({
    
            url: customURL,
 
        // Filters information before it is passed into the collection
        parse: function(response) {

            // Filter all tweets without a specific geolocation
            var filtered = _.filter(response.statuses, function(d) {
                            
                if (d.user.location !== null) {
                    return true;
                }

            });
            
            this.add(filtered);
            
        },
            
        initialize: function() {
          
          // Search for more tweets every 6 seconds
          setInterval(function() {
              console.log("Fetching fresh data...");
              console.log('Loading : '+customURL);
              tweets.fetch();
          }, 6000)
          
          this.fetch();
        }
  });
 


//... Model and Collection code ...//
 
 
//-- Views --------------------------------------------------------------------//
    
    var Map = Backbone.View.extend({
        
        el: $('#map_canvas'),
                        
        initialize: function() {
            
            var latlng = new google.maps.LatLng(0,0);
            // Google Maps Options
            var myOptions = {
  	            minZoom : 2,
                zoom: 3,
                center: latlng,
                mapTypeControl: false,
                navigationControlOptions: {
                style: google.maps.NavigationControlStyle.ANDROID
                },
                mapTypeId: google.maps.MapTypeId.TERRAIN,
                streetViewControl: false,
                styles: [{featureType:"administrative",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"landscape.natural",stylers:[{hue:"#0000ff"},{lightness:-84},{visibility:"off"}]},{featureType:"water",stylers:[{visibility:"on"},{saturation:-61},{lightness:-63}]},{featureType:"poi",stylers:[{visibility:"off"}]},{featureType:"road",stylers:[{visibility:"off"}]},{featureType:"administrative",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"landscape",stylers:[{visibility:"off"}]},{featureType:"administrative",stylers:[{visibility:"off"}]},{},{}]
              
            };
            
            // Force the height of the map to fit the window
            this.$el.height($(window).height() - $("header").height());
            
            // Add the Google Map to the page
            var map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);

            
            // Bind an event to add tweets from the collection
            
            this.collection.bind('add', function(model) {
                                       
                var user = model.get("user");
                var address = user.location;
                if(address==''){address=user.time_zone;}
                
 geocoder.geocode( { 'address': address}, function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {

                      var marker = new google.maps.Marker({
                                      map: map,
                                      position:results[0].geometry.location,
                                      animation:google.maps.Animation.DROP,
                                      icon: 'image/icon.png'
                              });
                       /* marker = new google.maps.Marker({
                                      map: map,
                                      position: results[0].geometry.location,
                                      animation:google.maps.Animation.DROP,
                                      icon: 'image/icon.png'
                              });*/
                        var infobox_style = "style='background-color:#222;padding:10px 10px 0 10px;font-size: 14px;width:350px;height:200px;font-weight:bold;color:#fff;'";
                        var contentString = "<div "+infobox_style+">"+
                        '<img style="width:50px;height:50px;padding: 3px 3px 3px 3px;" src="'+user.profile_image_url+'" />';
                        contentString+="<b>User  :</b>   "+user.screen_name+"<br> <br>";
                        contentString+="<b>Text</b> :</b>   "+model.get("text")+"<br><br>";
                        contentString+="<b>Time</b> :   "+model.get("created_at")+
                        "</div>";

                        var infowindow = new google.maps.InfoWindow({
                            content: contentString,
                        });
                                    
                        google.maps.event.addListener(marker, 'mouseover', function() {
                          infowindow.close();
                          infowindow.open(map,marker);
                        });

                        google.maps.event.addListener(marker, 'mouseout', function() {
                          infowindow.close();
                          
                        });
                  }
                  else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {    
                    /*If exhaustive search, wait for some time*/
                            setTimeout(function() {
                                sleep(10);
                            }, 200);
                  }
                else if (status === google.maps.GeocoderStatus.ZERO_RESULTS ) {    
                        setTimeout(function() {
                                sleep(10);
                            }, 200);
                  }
                  else {
                        sleep(10);
                        console.log('Geocode was not successful for the following reason: ' + status);
                } 
                 
                });

            });

        }

    }); //-- End of Map view

 
//... Mode, Collection, and View code ...//
 
 
//-- Initialize ---------------------------------------------------------------//
    
    // Create an instance of the tweets collection
    var tweets = new Tweets({
        model: Tweet
    });
 
    // Create the Map view, binding it to the tweets collection    
    var twitter_map = new Map({
        collection: tweets
    });
 
});
});

function sleep(milliseconds) {
    var i=0;
    var start = new Date().getTime();
    for (i = 0; i < 1e7; i++) {
      if ((new Date().getTime() - start) > milliseconds){
        return;
      }
    }
  }
