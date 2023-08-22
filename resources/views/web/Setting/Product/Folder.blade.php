<script>
    let Folders = @json($Folders);
    if (Folders.length > 0) {
        for (let i = 0; i < Folders.length; i++) {
            window.document.getElementById(Folders[i].id).click()
        }
    }
    function clearSendingGoodsArr() {
        window.document.getElementById('sendingGoodsArr').innerText = ""
        let children = $("#childrenProduct").children()
        for (let i = 0; i < children.length; i++) {
            if (children[i].style.display === 'none') {
                children[i].style.display = 'block'
            }
        }
    }
    function productItem(id, name) {
        const sendingGoodsArr = $('#sendingGoodsArr');

        if (name === 'Корневая папка') {
            clearSendingGoodsArr();
            sendingGoodsArr.append(`<input type="hidden" id="1" name="Folder ${id}" value="Folder${id}" class="customSpan">1) ${name}</input>`);
            $("#childrenProduct").children().css('display', 'none');
        } else {
            const childrenLength = sendingGoodsArr.children().length;
            const i = childrenLength > 0 ? childrenLength + 1 : 1;
            sendingGoodsArr.append(`<input type="hidden" id="${i}" name="Folder ${id}" value="Folder${id}" class="customSpan">${i}) ${name}</input>`);
            window.document.getElementById(id).style.display = "none";
        }
    }
    function FU_sendingGoods(value) {
        let view = window.document.getElementById('T1View')
        if (value === 1 || value === '1') {
            view.style.display = 'block'
            clearSendingGoodsArr()
        } else {
            view.style.display = 'none'
        }
    }
</script>
