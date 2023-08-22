<script>

    let Price = @json($Body_Price);
    if ((Price.salesPrices).length > 0) {
        const salesPrices = document.querySelector('select[name="salesPrices"]');
        const promotionalPrice = document.querySelector('select[name="promotionalPrice"]');

        function createOptions(data, targetElement) {
            data.forEach((item) => {
                const option = new Option(item.name, item.id);
                targetElement.appendChild(option);
            });
        }

        createOptions(Price.salesPrices, salesPrices);
        createOptions(Price.promotionalPrice, promotionalPrice);
        //SalesPriceHidden(Price.salesPrices[0].id, 'salesPrices');
    }


    /*  function SalesPriceHidden(value, nameSelector){
          function createOptions(value, data, targetElement) {
              data.forEach((item) => {
                  if (value !== item.id) {
                      const option = new Option(item.name, item.id);
                      targetElement.appendChild(option);
                  }
              });
          }
          const salesPrices = document.querySelector('select[name="salesPrices"]');
          const promotionalPrice = document.querySelector('select[name="promotionalPrice"]');



          if (nameSelector === 'salesPrices'){
              while (promotionalPrice.firstChild) { promotionalPrice.removeChild(promotionalPrice.firstChild); }
              createOptions(value, Price.promotionalPrice, promotionalPrice);
          }
          if (nameSelector === 'promotionalPrice'){
              while (salesPrices.firstChild) { salesPrices.removeChild(salesPrices.firstChild); }
              createOptions(value, Price.salesPrices, salesPrices);
          }
      }*/


</script>
