<html>
<head>
	<title>Catherine's Pizza Delivery</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
	<meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="pizza.css">
	<link href='https://fonts.googleapis.com/css?family=Questrial|Fjalla+One|Arimo|Josefin+Sans' rel='stylesheet' type='text/css'>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpMttyZ83MmTSnANmU8cwrfqijfkXSG3Y&libraries=places&callback=" async defer></script>
	<script type="text/javascript">
		var toGoogleTravelTime = false;
		var hardOrderNums = [];
		const fallBackPossibilities = ["Please continue...", "Ahh, tell me more.", "Yes, indeed indeed.", "Come, elucidate your thoughts", "Sorry, I didn't quite understand that. Do you mind repeating yourself again?"];
		var outputString = "";
		var dialogueCounter = 0;
		var previousResponse = "Hello there! How may I help you today? I can help you do the following things:";
		var accessToken = "3345970f25e64eaf9657ba5494ce0595";
		var baseUrl = "https://api.api.ai/v1/";
		$(document).ready(function() {
			setResponse(previousResponse);
			setEnhancedResponse(previousResponse);
			disableInput();
			$("#input").keypress(function(event) {
				if (event.which == 13) {
					event.preventDefault();
					send();
					$(this).val("");
				}
			});
			$("#rec").click(function(event) {
				send();
				$(this).val("");
			});
			/**Changes bot service based on user's initial selection of service. */
			$(window).load(function() {
				$("body").on('click', ".button", (function(event){
					event.preventDefault();
					var selection = $(this).attr('value');
					if (selection == "Edit Order") {
						accessToken = "def5c7f6f5f0410e90412f10fafb0e00";
					}
					send(selection);
					return;
				}));
				/**Carousel event handler goes here.*/
				loadCarousel();
				initAutocomplete()
			});

		});
		var recognition;
		function startRecognition() {
			recognition = new webkitSpeechRecognition();
			recognition.onstart = function(event) {
				updateRec();
			};
			recognition.onresult = function(event) {
				var text = "";
			    for (var i = event.resultIndex; i < event.results.length; ++i) {
			    	text += event.results[i][0].transcript;
			    }
			    setInput(text);
				stopRecognition();
			};
			recognition.onend = function() {
				stopRecognition();
			};
			recognition.lang = "en-US";
			recognition.start();
		}

		function stopRecognition() {
			if (recognition) {
				recognition.stop();
				recognition = null;
			}
			updateRec();
		}
		function switchRecognition() {
			if (recognition) {
				stopRecognition();
			} else {
				startRecognition();
			}
		}
		function setInput(text) {
			$("#input").val(text);
			send();
		}
		function updateRec() {
			$("#rec").text(recognition ? "Stop" : "Speak");
		}
		function send(userAction) {
			if (userAction == null) {
					var text = $("#input").val();
			} else {
				var text = userAction;
			}
			setUserResponse(text);
			$.ajax({
				type: "POST",
				url: baseUrl + "query/",
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				headers: {
					"Authorization": "Bearer " + accessToken
				},
				data: JSON.stringify({ q: text, lang: "en" }),
				success: function(data) {
					if (data.result.speech == null || data.result.speech == "") {
						fallbackResponse = "</br>" + fallBackPossibilities[Math.floor(Math.random())] + "</br><b>Catherine:</b></br>" + previousResponse;
						setResponse(fallbackResponse, text);
					} else {
						previousResponse = data.result.speech;
						normalResponse = data.result.speech;
						window.setTimeout(setResponse(normalResponse, text),1000);
						// setEnhancedResponse(normalResponse);
					}
				},
				error: function() {
					setResponse("Our system is currently down right now, sorry abou that! Please come back later.");
				}
			});
		}
		/**Function that loads carousel.*/
		/**Google address search box.*/
		function initAutocomplete() {
			$("body").on('click', '#pac-input', function(event) {
				var input = document.getElementById('pac-input');
				var searchBox = new google.maps.places.SearchBox(input);
				searchBox.addListener('places_changed', function() {
					var places = searchBox.getPlaces();
					if (places.length == 0) {
							return;
						}
					});
					$("#pac-input").keypress(function(event) {
						if (event.which == 13) {
							send($('#pac-input').val());
							$(this).val("");
						}
					});
			});
		}




		function loadCarousel() {
			$("body").on('click', "#left", function(event) {
				$("#carousel ul").animate({marginLeft:-480},"slow, 1000",function(){
					$(this).find("li:last").after($(this).find("li:first"));
					$(this).css({marginLeft:0});
				});
			 });
			 $("body").on('click', '#right', function(event) {
				$("#carousel ul").animate({marginLeft:480},"slow, 1000", function(){
					$(this).find("li:first").after($(this).find("li:last"));
					$(this).find("li:last").after($(this).find("li:first"));
					$(this).find("li:first").after($(this).find("li:last"));
					$(this).css({marginLeft:0});
				});
			 });
			 $("body").on('click', "img", (function(event){
				var selection = $(this).attr('value');
				send(selection);
				return;
				}));
		}
		/** Normal text responses from Catherine formatted here. */
		function setResponse(val, userResponse) {
			var enhancedResponse = setEnhancedResponse(val, userResponse);
			var innerResponse = "";
			if (val != "") {
				if (enhancedResponse != null) {
					innerResponse = "<b>Catherine:</b> " + val + enhancedResponse + "</br></br>"
					disableInput();
				} else {
					innerResponse = "<b>Catherine:</b> " + val + "</br></br>"
					enableInput();
				}
			}
			var spaceInner = document.getElementById('space');
			spaceInner.insertAdjacentHTML('beforeend', innerResponse);
			spaceInner.scrollTop = spaceInner.scrollHeight;
			return;
		}

		/**Outputs user's response on chat box.*/
		function setUserResponse(userResponse) {
			var innerResponse = "";
			if (userResponse != "") {
				innerResponse = "<b>You:</b> " + userResponse + "</br></br>"
			}
			var spaceInner = document.getElementById('space');
			spaceInner.insertAdjacentHTML('beforeend', innerResponse);
			spaceInner.scrollTop = spaceInner.scrollHeight;
			return;
		}

		/**Sets input box to read only to prevent user from entering text.*/
		function disableInput() {
			$('#input').attr('readonly', true);
			$('#input').css('background-color', 'rgba(211, 211, 211, 100)');
			$('#input').css('border-color', 'rgba(211, 211, 211, 0)');
		}

		/**Enables input box.*/
		function enableInput() {
			$('#input').attr('readonly', false);
			$('#input').css('background-color', '#ffffff');
			$('#input').css('border-color', 'initial');
		}

		/** Customzied responses processed here. */
		function setEnhancedResponse(botResponse, userResponse) {
			switch(botResponse) {
				case "Hello there! How may I help you today? I can help you do the following things:":
					return "<span></br><button class = 'button' id='place_order_button' value = 'Place Order'>Place Order</button><button class = 'button' id='edit_order_button' value = 'Edit Order'>Edit Order</button><button class = 'button' id='cancel_order_button' value='cancel Order'>Cancel Order</button></span>";
				case "Very well! We offer the following kinds of toppings: pepperoni, cheese, Hawaiian, and vegetarian. What would you like?":
					return "<div id = 'carousel'>" +
					  "<ul>"+
					    "<li ><img src='assets/pepperoni.jpg' width='300px' value='Pepperoni pizza.'><div class = 'topping'></div></img></li><li><img src='assets/cheese.jpg' width='300px' value='Cheese pizza.'><div class = 'topping'></div></img></li><li><img src='assets/hawaiian.jpg' 'width=300px' value='Hawaiian pizza.'><div class = 'topping'></div></img></li><li><img src='assets/vegetarian.jpg' width='300px' value='Vegetarian pizza.'><div class = 'topping'></div></img></li>" +
					  "</ul>" +
					"</div>" +
					"<div class = 'car_nav' id = 'left'>&#9664;</div>"+
					"<div class = 'car_nav' id = 'right'>&#9654;</div>"+
					"</div>";
			}

			if (botResponse.includes("Would you be interested in customizing your pizza?")) {
				return "<span></br><button class = 'button' id='yes_button' value = 'Yes'>Yes</button><button class = 'button' id='no_button' value = 'No'>No</button></span>";
			}
			if (botResponse.includes("Would you like your crust thick or thin?")) {
				return "<span></br><button class = 'button' id='thick_button' value = 'Thick'>Thick</button><button class = 'button' id='thin_button' value = 'Thin'>Thin</button></span>";
			}
			if (botResponse.includes("Would you like the pizza to be cut square or round?")) {
				return "<span></br><button class = 'button' id='square_button' value = 'Square'>Square</button><button class = 'button' id='round_button' value = 'Round'>Round</button></span>";
			}
			if (botResponse.includes("Very well. Would you like your pizza baked normally or extra baked?")) {
				return "<span></br><button class = 'button' id='normally_baked_button' value = 'Normally baked.'>Normally Baked</button><button class = 'button' id='extra_baked_button' value = 'Extra Baked'>Extra Baked</button></span>";
			}
			if (botResponse.includes("Very well. Would you like your pizza baked normally or extra baked?")) {
				return "<span></br><button class = 'button' id='normally_baked_button' value = 'Normally baked.'>Normally Baked</button><button class = 'button' id='extra_baked_button' value = 'Extra Baked'>Extra Baked</button></span>";
			}
			if (botResponse.includes("Where should I deliver it to?")) {
				return "<input id='pac-input' class='controls' type='text' placeholder='Search Box'>"
			}

			// if (botResponse.includes("Where should I deliver it to?")) {
			// 	toGoogleTravelTime = true;
			// 	return
			// }
			// if (botResponse.includes("Please enter your phone number to confirm.")) {
			// 	return "<span><input id='phone_p1'></input>-<input id='phone_p2'></input>-<input id='phone_p3'></input></span>";
			// }
		}


		/**Jumps cursor to the next input box.*/
		function p1ToP2() {
				if ($('#phone_p1').val().length > 3) {
					$('#phone_p2').focus();
				}
		}

		function p2ToP3() {
				if ($('#phone_p2').val().length > 4) {
					$('#phone_p3').focus();
				}
		}


	</script>
</head>
<body>
	<?php include 'payment.php';?>
  <div id = "title"><h1>Catherine's Pizza Delivery</h1></div>
  <div id = "menu">
    <div class = "option">Contact Us</div>
    <div class = "option" id = "chat_tab">Chat Bot Service</div>
    <div class = "option">Reviews</div>
    <div class = "option">Make a Reservation</div>
	  <div class = "option">Menu</div>
  </div>
</br>
	<div id = "apiinput">
		<div id = "space"></div>
    <input id="input" color="white" readonly="false" autocomplete="off" type="text"> <button id="rec">Send</button>
	</div>

</body>
</html>
