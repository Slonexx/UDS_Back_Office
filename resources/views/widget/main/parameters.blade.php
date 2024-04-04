<script>
    const url = "{{ Config::get("Global.url") }}"

    let accountId = "{{ $accountId }}"

    let extensionPoint
    let GlobalobjectId

    let GlobalxRefURL
    let GlobalUDSOrderID
    let OLDPhone
    let OLDQRCode

    let tmp_operations_style
    let operations_total
    let operations_cash
    let operations_points
    let operations_Max_points
    let operations_availablePoints
    let operations_availablePoints_Nubmer
    let operations_skipLoyaltyTotal
    let operations_user
    let operations_user_uid
    let cashBack
    let operations_cashier_id = "{{ $cashier_id }}"
    let operations_cashier_name = "{{ $cashier_name }}"

    let operationsAccrue
    let operationsCancellation

     /*let receivedMessage = {
         "name": "Open",
         "extensionPoint": "document.customerorder.edit",
         "objectId": "5f3023e9-05b3-11ee-0a80-06f20001197a",
         "messageId": 1,
         "displayMode": "expanded"
     }*/

</script>
