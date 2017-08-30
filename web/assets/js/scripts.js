/**
 * Created by khuen on 19-Aug-17.
 */

var lastUserInput = "";
var textToSpeech = false;
var timeout = setTimeout(bluffing, 120000);
var userDataInput;

jQuery(document).ready(function () {
    // Bot response
    jQuery.ajax({
        method: 'GET',
        url: jQuery("#userdataexist-url").val(),
        dataType: 'json',
        success: function (data) {
            if (parseInt(data.has_user_data) === 0) {
                userDataInput = new jBox('Modal', {
                    width: 400,
                    height: 450,
                    title: 'Hãy cho M.I.R.I biết bạn là ai',
                    animation: 'tada',
                    theme: 'NoticeFancy',
                    closeOnEsc: false,
                    closeOnClick: false,
                    content: jQuery("#user-info-box")
                });
                userDataInput.open();
            }
        }
    });
});

/**
 * Talk some nonsense
 */
function bluffing() {
    jQuery("#botres").append("<div id='tenor'></div>");
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
 * Post user data
 */
function postUserData() {
    var form = jQuery('#user-info-form');
    var url = form.attr('action');

    jQuery.ajax({
        method: 'GET',
        url: url,
        data: form.serialize(),
        dataType: 'json',
        success: function (data) {
            if (userDataInput !== undefined && parseInt(data.done) === 1) {
                userDataInput.close();
            } else {
                alert("Đã xảy ra lỗi!");
            }
        },
        failed: function (data) {
            alert("Đã xảy ra lỗi!");
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