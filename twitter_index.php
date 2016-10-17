<html>
	<head>
		<title>Twitter</title>

		<!--<script type="text/javascript" src="./js/jquery-2.1.4.js"></script>-->
		<script src="js/jquery.js"></script>
	</head>

	<body>
		<h2>Twitter</h2>
		<div>
			<ul class="ul-usertimeline"></ul>
		</div>
	</body>
</html>
<script type="text/javascript">
	
	/*$.get("https://api.twitter.com/1.1/statuses/user_timeline.json", function( data ) {
	  console.log(data)
	  //$( ".result" ).html( data );
	  console.log( "Load was performed." );
	});*/

	$.get("./twitter/get_user_timeline.php", function (data) {
	    console.log(data);

	    for(var i=0; i<data.length; i++)
	    {
	    	//FILTER by month
	    	//var a = new Date(data[i].created_at);	
	    	//if(a.getMonth()==7){ 
	    	//FILTER retweeted  
	    	//if(data[i].retweeted_status != null){
	    	
	    	var text = 	"<li>"+
	    				"<div>"+i+"</div>"+
	    				"<div>Time and Date of Tweet: "+data[i].created_at+"</div>"+
	    				"<div>Tweet: "+data[i].text+"</div>"+
	    				"<div>Tweeted by: "+data[i].user.name+"</div>"+
	    				"<div>Screen name: "+data[i].user.screen_name+"</div>"+
	    				"</li>";
	    	$(".ul-usertimeline").append(text);
	    }
	    
	},'json');
	/*$.get("./twitter_ref/get_tweet_by_id.php", function (data) {
	    console.log(data);    
	},'json');*/
</script>