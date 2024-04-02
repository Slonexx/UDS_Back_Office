<script>
    function ButtonComplete() {
        let settings = ajax_settings(url + "CompletesOrder/{{$accountId}}/" + GlobalUDSOrderID, "GET", null)
        console.log('Button Complete request settings  ↓ ')
        console.log(settings)
        $.ajax(settings).done(function (response) {
            console.log('Button Complete request response  ↓ ')
            console.log(response)
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
