Welcome<?=  !empty($name) ? ' ' . $name : ''  ?>!

<script>

    const stores = [{_id:1,name:'mpowerpromo.com'},{_id:2,name:'amazon.com'}];
    const categories=[{name:'Aparel',_id:1,stores:[1]},{name:'Bag',_id:2,stores:[1,2]},];
    const facetGroup=[{_id:1,name:'Brand'},{_id:2,name:'Color'}];
    const fasets = [{_id:1, name:'nike', facetGroup:1},{_id:2, name:'Black', facetGroup:2}];
    const products=[{productId:'T-shrirt',categories:[1,2],facets:[1,2],_id:1},{productId:'school bag',facets:[2],categories:[1],_id:2},];

    function getProducts(filters){
        let f = {}

        function setParent(dataArr, fKey){
            if(typeof filters[fKey] !== 'undefined'){
                let filtered = dataArr.filter((itm) => {
                    return itm.name.trim().toLowerCase() == filters[fKey].trim().toLowerCase();
                });

                if(filtered.length > 0){
                    f[fKey] = filtered.map((itm) => {
                        return itm._id;
                    })
                }
            }
        }

        function setChild(dataArr, fKey, fKeyParent, dataKeyParent){
            let filtered = dataArr.filter((itm) => {
                if(typeof filters[fKey] !== 'undefined') {
                    let isEq = itm.name.trim().toLowerCase() == filters[fKey].trim().toLowerCase();
                    if (!isEq)
                        return false;
                }

                if(typeof f[fKeyParent] === 'undefined')
                    return true;

                for(let idx in f[fKeyParent]){
                    let f_itm = f[fKeyParent][idx];
                    let passes = Array.isArray(itm[dataKeyParent]) ? itm[dataKeyParent].includes(f_itm) : (itm[dataKeyParent] == f_itm);
                    if(passes)
                        return true;
                }

                return false;
            });

            f[fKey] = filtered.length === 0 ? [] : filtered.map((itm) => {
                return itm._id;
            });
        }

        [
            {data: stores, fKey: 'store'},
            {data: categories, fKey: 'category', fKeyParent: 'store', dataKeyParent: 'stores'},
            {data: facetGroup, fKey: 'facetGroup'},
            {data: fasets, fKey: 'faset', fKeyParent: 'facetGroup', dataKeyParent: 'facetGroup'},
        ].forEach((itm) => {
            if(typeof itm.fKeyParent === 'undefined' || typeof itm.dataKeyParent === 'undefined'){
                setParent(itm.data, itm.fKey);
            }else{
                setChild(itm.data, itm.fKey, itm.fKeyParent, itm.dataKeyParent);
            }
        });

        let productsFiltered = products.filter((product) => {
            let filterData = [
                {fKey: 'category', productFilterKey: 'categories'},
                {fKey: 'faset', productFilterKey: 'facets'},
            ];

            for(let idx in filterData) {
                let filterRow = filterData[idx];

                if(typeof f[filterRow.fKey] !== 'undefined'){
                    let passed = false;
                    for(let idxx in f[filterRow.fKey]){
                        let f_item = f[filterRow.fKey][idxx];
                        if(product[filterRow.productFilterKey].includes(f_item)){
                            passed = true;
                            break;
                        }
                    }
                    if(!passed)
                        return false;
                }
            }

            return true;
        });

        return productsFiltered;
    }

    let ff = {
        // category: 'Aparel',
        // category: 'Bag',
        // store: 'mpowerpromo.com',
        store: 'amazon.com',
        facetGroup: 'Color',
        // faset: 'nike',
        // Black
    };

    let res = getProducts(ff);

    console.log(res);


    // const stores = [{_id:1,name:'mpowerpromo.com'},{_id:2,name:'amazon.com'}];
    // const categories=[{name:'Aparel',_id:1,stores:[1]},{name:'Bag',_id:2,stores:[1,2]},];
    // const facetGroup=[{_id:1,name:'Brand'},{_id:2,name:'Color'}];
    // const fasets = [{_id:1, name:'nike', facetGroup:1},{_id:2, name:'Black', facetGroup:2}];
    // const products=[{productId:'T-shrirt',categories:[1,2],facets:[1,2],_id:1},{productId:'school bag',facets:[2],categories:[1],_id:2},];


    // const facetGroup=[{_id:1,name:'Brand'},{_id:2,name:'Color'}];

    // console.log(f);
    // console.log(res);

    //create a function which return:
    //1. List of all Bag products for mpowerpromo.com
    //2. List of all products for amazon.com
    //3. List of all Aparel nike products for amazon.com
    //3. List of all Color products for amazon.com

    //Require to render in html view instead `Your results here`, prefer to use native js features, you are welcome to use libraries if its easy for you

</script>