<script>
    function onchangePoint() {
        let QRCodePoint = window.document.getElementById('QRCodePoint');
        operations_points = QRCodePoint.value
    }

    function formatParams(params) {
        return "?" + Object
            .keys(params)
            .map(function (key) {
                return key + "=" + encodeURIComponent(params[key])
            })
            .join("&")
    }

    function only_numbers() {
        if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;
    }

    function only_float() {
        if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = event.keyCode === 46;
    }
</script>

{{--OLD CODE--}}
<script>
    /*
    document.getElementById("QRCodePoint").addEventListener("change", function () {
        let v = parseInt(this.value);
        if (v < 1) this.value = 1;
        if (v > operations_Max_points) this.value = operations_Max_points;
        operations_points = this.value
        });
    */
</script>
