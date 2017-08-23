/**
 * Created by khuen on 19-Aug-17.
 */

var lastUserInput = "";

/**
 * Input message to the bot
 * @param el
 * @param event
 */
function inputMessage(el, event) {
    var botres = jQuery("#botres");
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
            botres.append("[User] >>> " + userInput + "<br/>");
            botres.scrollTop(botres.scrollHeight);
            botres.animate({ scrollTop: botres[0].scrollHeight}, 500);

            jQuery.ajax({
                method: 'POST',
                url: jQuery("#answer-url").val(),
                data: {input: userInput},
                dataType: 'json',
                success: function(data) {
                    botres.append("[Miri] >>> " + data.answer + "<br/>");
                }
            });

            // Clear input
            el.val("");
        } else {
            el.append("<br/>");
        }

        lastUserInput = userInput;
    }


}