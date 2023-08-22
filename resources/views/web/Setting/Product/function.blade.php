<script>

    function ViewBlockHide(value) {
        if (value === '0'){
            window.document.getElementById('T2View').style.display = 'block'
        } else  window.document.getElementById('T2View').style.display = 'none'
    }
    function clearBaseUDS() {
        $('#clearModel').modal('hide')
        console.log(url + ' data ↓ ')

        $.ajax({
            url: url,
            method: 'post',
            dataType: 'json',
            data: null,
            success: function(response){
                $('#clearModel').modal('hide')
                console.log(url + ' response ↓ ')
                console.log(response)
            }
        });
    }
    function hideClearModel() { $('#clearModel').modal('hide') }
    function activateClearModel() { $('#clearModel').modal('show') }


</script>
