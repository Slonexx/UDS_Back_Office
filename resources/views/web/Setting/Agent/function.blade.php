<script>

    function FU_sendingGoods(value) {
        let view = window.document.getElementById('T1View')
        if (value === 1 || value === '1') {
            view.style.display = 'block'
        } else {
            view.style.display = 'none'
        }
    }
    function toggleClick(id) {

        if (id === 1) {
            let toggle_off = window.document.getElementById('toggle_off')
            let toggle_on = window.document.getElementById('toggle_on')

            let T1 = window.document.getElementById('uds_data')

            if (toggle_off.style.display == "none") {
                toggle_on.style.display = "none"
                toggle_off.style.display = "block"

                T1.style.display = 'block'
            } else {
                toggle_on.style.display = "block"
                toggle_off.style.display = "none"

                T1.style.display = 'none'
            }
        }

        if (id === 2) {
            let toggle_off_2 = window.document.getElementById('toggle_off_2')
            let toggle_on_2 = window.document.getElementById('toggle_on_2')

            let T2 = window.document.getElementById('update_uds_data')
            if (toggle_off_2.style.display == 'none') {
                toggle_on_2.style.display = "none"
                toggle_off_2.style.display = "block"

                T2.style.display = 'block'
            } else {
                toggle_on_2.style.display = "block"
                toggle_off_2.style.display = "none"

                T2.style.display = 'none'
            }
        }


    }

    $('.myPopover1').popover();
    $('.myPopover2').popover();
    $('.myPopover3').popover();
    $('.myPopover4').popover();
    $('.myPopover5').popover();
    $('.myPopover6').popover();
</script>
