<script>

    let Store = @json($Body_store);
    if ((Store).length > 0) {
        const element = document.querySelector('select[name="Store"]');

        function createOptions(data, targetElement) {
            data.forEach((item) => {
                const option = new Option(item.name, item.id);
                targetElement.appendChild(option);
            });
        }

        createOptions(Store, element);
    }


</script>
