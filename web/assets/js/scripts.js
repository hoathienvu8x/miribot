/**
 * Created by khuen on 19-Aug-17.
 */

var lastUserInput = "";
var textToSpeech = true;
var timeout = setTimeout(bluffing, 120000);

function bluffing() {
    requestAnswer("*");
    clearTimeout(timeout);
    timeout = setTimeout(bluffing, 120000);
}

/**
 * Input message to the bot
 * @param el
 * @param event
 */
function inputMessage(el, event) {
    clearTimeout(timeout);
    timeout = setTimeout(bluffing, 120000);

    var botres = jQuery("#botres");
    var botport = jQuery("#botport");

    el = jQuery(el);
    var userInput = el.val();

    if (event.keyCode === 38) {
        el.val(lastUserInput);
    }

    if (event.keyCode === 13) {

        if (!event.shiftKey) {
            // Block default action
            event.preventDefault();

            // Send the message to server and request for answer
            botres.append("<div class='conversation user-input'><b>[User] >>></b> " + userInput + "</div>");
            botres.append("<div id='tenor'></div>");
            botres.scrollTop(botres.scrollHeight);
            botres.animate({scrollTop: botres[0].scrollHeight}, 500);
            requestAnswer(userInput);

            // Clear input
            el.val("");
        } else {
            el.append("<br/>");
        }

        lastUserInput = userInput;
    }
}

function requestAnswer(userInput) {
    var botres = jQuery("#botres");
    var botport = jQuery("#botport");

    // Bot response
    jQuery.ajax({
        method: 'POST',
        url: jQuery("#answer-url").val(),
        data: {input: userInput},
        dataType: 'json',
        success: function (data) {
            botres.append("<div class='conversation bot-answer'><b>[Miri] >>></b> " + data.answer + "</div>");
            var portrait = data.emotion + ".png";
            jQuery("#tenor").remove();
            botres.animate({scrollTop: botres[0].scrollHeight}, 500);
            botport.css("background", 'url(../assets/portraits/' + portrait + ')');
            if (textToSpeech) {
                responsiveVoice.speak(data.answer, "Vietnamese Male", {pitch: 1.3, volume: 3});
            }
        }
    });
}

/**
 * Clear the chat panel
 */
function clearChat() {
    jQuery(".conversation").animate({opacity: 0}, 500, function () {
        this.remove();
    });
}

/**
 * Get random emotion
 */
function randomEmotion() {
    var emolist = [
        "angry",
        "cute",
        "default",
        "doubtful",
        "happy",
        "joyful",
        "neutral",
        "nope",
        "sad",
        "scared",
        "surprise",
        "thoughtful",
        "serious",
        "searchful",
        "shy"
    ];

    var portrait = emolist[Math.floor(Math.random() * emolist.length)];
    jQuery("#botport").css("background", 'url(../assets/portraits/' + portrait + '.png)');
}

/**
 * Toggle text to speech
 */
function ttsToggle() {
    textToSpeech = !textToSpeech;
    var ttsBtn = jQuery("#toggle-tts");

    if (textToSpeech) {
        ttsBtn.text("Im đi coi").addClass("btn-danger").removeClass("btn-default");
    } else {
        ttsBtn.text("Nói đi coi").addClass("btn-default").removeClass("btn-danger");
    }
}