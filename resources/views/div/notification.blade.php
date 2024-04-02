<div id="notification" class="notification" style="display: none">
    <div id="notification_message"></div>
</div>
<script>
    new MutationObserver(
        function (mutationsList, observer) {
            if (notification_message.innerText.trim() !== '') notification.style.display = 'block';
            else notification.style.display = 'none';
        }
    ).observe(notification_message, {subtree: true, characterData: true, childList: true});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let message_json = "{{$message}}" ?? ""


        const notification = document.getElementById('notification');
        const notification_message = document.getElementById('notification_message');


        if (message_json != '') {
            let message_class = "{{$class_message}}" ?? ""
            if (message_json != '') setNotification(message_class, message_json)
            else setNotification('is-info', message_json)
        }

        function setNotification(name_class, text) {
            notification_message.innerText = ''
            notification_message.innerText = text

            notification.classList.remove()
            notification.classList.add('notification')
            notification.classList.add(name_class)
        }


    });
</script>
