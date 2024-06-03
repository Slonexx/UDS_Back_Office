<script>
    function ButtonComplete() {
        window.document.getElementById('ButtonComplete').style.display = 'none'
        let settings = ajax_settings(url + "CompletesOrder/{{$accountId}}/" + GlobalUDSOrderID, "GET", null)
        $.ajax(settings).done(function (response) {
            window.document.getElementById('ButtonComplete').style.display = 'block'
            if (response.StatusCode == 200) {
                document.getElementById("success").style.display = "block";
                document.getElementById("danger").style.display = "none";
            } else {
                document.getElementById("success").style.display = "none";
                document.getElementById("danger").style.display = "block";
            }
        });
    }
</script>
