<script>


    let unloading = "{{ $unloading }}"

    FU_sendingGoods(unloading)
    if (unloading == '1') {
        document.querySelector('select[name="unloading"]').value = unloading

        document.querySelector('select[name="examination"]').value = "{{ $examination }}"
        document.querySelector('select[name="email"]').value = "{{ $email }}"
        document.querySelector('select[name="gender"]').value = "{{ $gender }}"
        document.querySelector('select[name="birthDate"]').value = "{{ $birthDate }}"

    }


</script>
