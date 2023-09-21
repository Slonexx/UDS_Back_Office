<script>

    let accountId = "{{ $accountId }}"
    let url = "https://smartuds.kz/api/DeleteALLProductForUDSController/p330538/"+accountId



    let setProductFolder = '{{$ProductFolder}}'
    let setUnloading = '{{$unloading}}'
    let setSalesPrices = '{{$salesPrices}}'
    let setPromotionalPrice = '{{$promotionalPrice}}'
    let setStore = '{{$Store}}'
    let setStoreRecord = '{{$StoreRecord}}'
    let setProductHidden = '{{$productHidden}}'

    window.document.querySelector('select[name="ProductFolder"]').value = setProductFolder

    FU_sendingGoods(setProductFolder)

    let Folders = @json($Folders);
    if (Folders.length > 0) {
        for (let i = 0; i < Folders.length; i++) {
            window.document.getElementById(Folders[i].id).click()
        }
    }

    if (setProductFolder === '1') {
        window.document.querySelector('select[name="unloading"]').value = setUnloading
        window.document.querySelector('select[name="salesPrices"]').value = setSalesPrices
        window.document.querySelector('select[name="promotionalPrice"]').value = setPromotionalPrice
        window.document.querySelector('select[name="Store"]').value = setStore
        window.document.querySelector('select[name="StoreRecord"]').value = setStoreRecord
        window.document.querySelector('select[name="productHidden"]').value = setProductHidden

        ViewBlockHide(setUnloading)
    }




</script>
