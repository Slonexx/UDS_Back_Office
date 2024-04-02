<script>
    function PointMax(max) { maxPoint.innerText = max.toString(); }

    document.getElementById("QRCodePoint").addEventListener("change", function () {
        let v = parseInt(this.value);
        this.value = Math.max(1, Math.min(v, operations_Max_points));
        operations_points = this.value;
    });




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
