<script>
    function ajax_settings(url, method, data) {
        return {
            "url": url,
            "method": method,
            "timeout": 0,
            "headers": {"Content-Type": "application/json",},
            "data": data,
        }
    }


    function xRefURL() {
        window.open(GlobalxRefURL);
    }



    function setStateByStatus(State) {
        if (State == "NEW") {
            document.getElementById("ButtonComplete").style.display = "block";
            document.getElementById("Complete").style.display = "none";
            document.getElementById("Deleted").style.display = "none";
        }
        if (State == "COMPLETED") {
            document.getElementById("Complete").style.display = "block";
            document.getElementById("ButtonComplete").style.display = "none";
            document.getElementById("Deleted").style.display = "none";
        }
        if (State == "DELETED") {
            document.getElementById("Deleted").style.display = "block";
            document.getElementById("Complete").style.display = "none";
            document.getElementById("Complete").style.display = "none";
        }
    }

    function set_extensionPoint(params) {
        let result
        switch (params) {
            case "document.customerorder.edit": {
                result = "customerorder"
                extensionPoint = "customerorder"
                break
            }

            case "document.demand.edit": {
                result = "demand"
                extensionPoint = "demand"
                break
            }

            case "document.salesreturn.edit": {
                result = "salesreturn"
                extensionPoint = "salesreturn"
                break
            }
        }
        return result
    }
</script>
