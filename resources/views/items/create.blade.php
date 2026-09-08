@extends('layouts.dashboard')

@section('title', 'List an Item')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            List an Item
        </h1>
        <p class="text-gray-600 mt-1">
            Add clear details so renters can trust and book confidently.
        </p>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

        <form
            method="POST"
            action="{{ route('items.store') }}"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf

            {{-- Item Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Item Name
                </label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="Wedding Chairs, DJ Speakers, Decoration Set"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Item Description
                </label>
                <textarea
                    name="description"
                    rows="4"
                    required
                    placeholder="Describe the item, condition, size, usage rules, delivery details..."
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 resize-y"
                >{{ old('description') }}</textarea>
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Category
                </label>
                <select
                    name="category_id"
                    id="categorySelect"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select category</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach

                    <option value="other">Others</option>
                </select>
            </div>

            {{-- Other Category --}}
            <div id="otherCategoryBox" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Specify Category
                </label>
                <input
                    type="text"
                    name="other_category"
                    value="{{ old('other_category') }}"
                    placeholder="e.g. Stage Lighting, Event Backdrop"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                <p class="text-xs text-gray-500 mt-1">
                    Category must be related to event or wedding rentals.
                </p>
            </div>
			
			{{-- ITEM MODE --}}
<div class="mb-4">
    <label class="form-label fw-bold">How do you want to list this item?</label>

    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               name="listing_type"
               id="rent"
               value="rent">

        <label class="form-check-label" for="rent">
            Rent-out this item
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               name="listing_type"
               id="sell"
               value="sell">

        <label class="form-check-label" for="sell">
            Sell this item
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               name="listing_type"
               id="both"
               value="both">

        <label class="form-check-label" for="both">
            Rent AND Sell
        </label>
    </div>
</div>

            {{-- Pricing Section --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- RENT PRICE --}}
    <div id="rent_price_section">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Price Per Day (₦)
        </label>

        <input
            type="number"
            name="price_per_day"
            value="{{ old('price_per_day') }}"
            min="1"
            placeholder="e.g. 5000"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
        >
    </div>

    {{-- SELL PRICE --}}
    <div id="sell_price_section">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Selling Price (₦)
        </label>

        <input
            type="number"
            name="selling_price"
            value="{{ old('selling_price') }}"
            step="0.01"
            placeholder="Enter selling price"
            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
        >
    </div>

</div>

{{-- ORIGINAL PRICE --}}
<div id="original_price_section" class="mt-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Original Price (Optional)
    </label>

    <input
        type="number"
        name="original_price"
        value="{{ old('original_price') }}"
        step="0.01"
        placeholder="Enter original price if discounted"
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
    >
</div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Location
                    </label>
                    <input
                        type="text"
                        name="location"
                        value="{{ old('location') }}"
                        required
                        placeholder="Near Ikeja City Mall"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
						
                {{-- State --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        State
                    </label>
                    <select
    name="state"
    id="state"
    required
    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
>
    <option value="">Select State</option>

    <option>Abia</option>
    <option>Adamawa</option>
    <option>Akwa Ibom</option>
    <option>Anambra</option>
    <option>Bauchi</option>
    <option>Bayelsa</option>
    <option>Benue</option>
    <option>Borno</option>
    <option>Cross River</option>
    <option>Delta</option>
    <option>Ebonyi</option>
    <option>Edo</option>
    <option>Ekiti</option>
    <option>Enugu</option>
    <option>FCT</option>
    <option>Gombe</option>
    <option>Imo</option>
    <option>Jigawa</option>
    <option>Kaduna</option>
    <option>Kano</option>
    <option>Katsina</option>
    <option>Kebbi</option>
    <option>Kogi</option>
    <option>Kwara</option>
    <option>Lagos</option>
    <option>Nasarawa</option>
    <option>Niger</option>
    <option>Ogun</option>
    <option>Ondo</option>
    <option>Osun</option>
    <option>Oyo</option>
    <option>Plateau</option>
    <option>Rivers</option>
    <option>Sokoto</option>
    <option>Taraba</option>
    <option>Yobe</option>
    <option>Zamfara</option>
</select>

                </div>

                {{-- Local Government --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Local Government
                    </label>
                    <select
                        name="local_government"
                        id="lga"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Select LGA</option>
                    </select>
                </div>

            </div>
			
			            {{-- Quantity Available --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Quantity Available
                </label>
                <input
                    type="number"
                    name="quantity_units"
                    value="{{ old('quantity_units') }}"
                    min="1"
                    required
                    placeholder="How many units do you have available?"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                <p class="text-xs text-gray-500 mt-1">
                    Total number of units available for rent (e.g., 50 chairs, 10 tables)
                </p>
            </div>

           {{-- Rental Package System --}}
<div class="mt-6">

    <h3 class="text-lg font-semibold text-gray-800 mb-3">
        Rental Package
    </h3>

    <p class="text-sm text-gray-500 mb-4">
        Define how this item is rented. Example: 12 chairs per dozen.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- UNITS PER PACKAGE --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Units per package
            </label>

            <input
                type="number"
                name="unit_size"
                value="{{ old('unit_size') }}"
                min="1"
                placeholder="e.g. 12"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
        </div>

        {{-- UNIT LABEL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Unit label
            </label>

            <input
                type="text"
                name="unit_label"
                value="{{ old('unit_label') }}"
                placeholder="e.g. dozen, pack, bundle"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
        </div>

    </div>

</div>

{{-- IMAGE UPLOADER --}}
<div class="mt-6">

<label class="block text-sm font-medium text-gray-700 mb-3">
Item Photos
</label>

<div id="image_preview_grid"
     class="grid grid-cols-2 md:grid-cols-4 gap-4">

    {{-- ADD BUTTON TILE --}}
    <label id="add_photo_tile"
           class="flex items-center justify-center border-2 border-dashed
                  border-gray-300 rounded-xl h-32 cursor-pointer
                  hover:border-blue-500 transition">

        <span class="text-gray-500 text-sm">
            + Add Photo
        </span>

        <input type="file"
               id="image_input"
               name="images[]"
               multiple
               accept="image/*"
               class="hidden">

    </label>

</div>

<p class="text-xs text-gray-500 mt-2">
Upload multiple photos to attract more renters.
</p>

</div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="{{ route('items.index') }}" class="text-sm text-gray-600 hover:underline">
                    Cancel & return home
                </a>

                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-blue-700 transition"
                >
                    Save Item
                </button>
            </div>
        </form>
		
		<script>

const input = document.getElementById("image_input");
const grid = document.getElementById("image_preview_grid");
const addTile = document.getElementById("add_photo_tile");

let filesArray = [];

input.addEventListener("change", function(){

    const files = Array.from(this.files);

    files.forEach(file => {

        filesArray.push(file);

        const reader = new FileReader();

        reader.onload = function(e){

            const card = document.createElement("div");
            card.classList.add("relative","group");

            const img = document.createElement("img");
            img.src = e.target.result;

            img.classList.add(
                "w-full",
                "h-32",
                "object-cover",
                "rounded-xl",
                "border",
                "cursor-pointer",
                "transition",
                "hover:scale-105"
            );

            img.onclick = function(){
                window.open(e.target.result, "_blank");
            };

            const remove = document.createElement("button");
            remove.innerHTML = "×";

            remove.classList.add(
                "absolute",
                "top-1",
                "right-1",
                "bg-black",
                "bg-opacity-60",
                "text-white",
                "rounded-full",
                "w-6",
                "h-6",
                "text-xs",
                "hidden",
                "group-hover:flex",
                "items-center",
                "justify-center"
            );

            remove.onclick = function(){
                card.remove();
                filesArray = filesArray.filter(f => f !== file);
            };

            card.appendChild(img);
            card.appendChild(remove);

            grid.insertBefore(card, addTile);

        };

        reader.readAsDataURL(file);

    });

});

</script>
		
		<script>

document.addEventListener("DOMContentLoaded", function(){

    const radios = document.querySelectorAll('input[name="listing_type"]');

    radios.forEach(function(radio){

        radio.addEventListener("change", function(){

            const rent = document.getElementById("rent_price_section");
            const sell = document.getElementById("sell_price_section");
            const original = document.getElementById("original_price_section");

            if(this.value === "rent"){
                rent.style.display = "block";
                sell.style.display = "none";
                original.style.display = "block";
            }

            if(this.value === "sell"){
                rent.style.display = "none";
                sell.style.display = "block";
                original.style.display = "block";
            }

            if(this.value === "both"){
                rent.style.display = "block";
                sell.style.display = "block";
                original.style.display = "block";
            }

        });

    });

});

</script>

<script>

const imageInput = document.getElementById('image_input');
const preview = document.getElementById('image_preview');

if(imageInput){
    imageInput.addEventListener('change', function(){

        const file = this.files[0];

        if(file){

            const reader = new FileReader();

            reader.onload = function(e){
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };

            reader.readAsDataURL(file);

        }

    });
}

</script>

{{-- Category Toggle Script --}}
<script>
    const select = document.getElementById('categorySelect');
    const otherBox = document.getElementById('otherCategoryBox');

    select.addEventListener('change', function () {
        otherBox.classList.toggle('hidden', this.value !== 'other');
    });

    // LGA Dynamic
   const lgaData = {

"Abia": ["Aba North","Aba South","Arochukwu","Bende","Ikwuano","Isiala Ngwa North","Isiala Ngwa South","Isuikwuato","Obingwa","Ohafia","Osisioma","Ugwunagbo","Ukwa East","Ukwa West","Umuahia North","Umuahia South","Umu Nneochi"],

"Adamawa": ["Demsa","Fufure","Ganye","Gayuk","Gombi","Grie","Hong","Jada","Lamurde","Madagali","Maiha","Mayo Belwa","Michika","Mubi North","Mubi South","Numan","Shelleng","Song","Toungo","Yola North","Yola South"],

"Akwa Ibom": ["Abak","Eastern Obolo","Eket","Esit Eket","Essien Udim","Etim Ekpo","Etinan","Ibeno","Ibesikpo Asutan","Ibiono-Ibom","Ika","Ikono","Ikot Abasi","Ikot Ekpene","Ini","Itu","Mbo","Mkpat-Enin","Nsit-Atai","Nsit-Ibom","Nsit-Ubium","Obot Akara","Okobo","Onna","Oron","Oruk Anam","Udung-Uko","Ukanafun","Uruan","Urue-Offong/Oruko","Uyo"],

"Anambra": ["Aguata","Anambra East","Anambra West","Anaocha","Awka North","Awka South","Ayamelum","Dunukofia","Ekwusigo","Idemili North","Idemili South","Ihiala","Njikoka","Nnewi North","Nnewi South","Ogbaru","Onitsha North","Onitsha South","Orumba North","Orumba South","Oyi"],

"Bauchi": ["Alkaleri","Bauchi","Bogoro","Damban","Darazo","Dass","Gamawa","Ganjuwa","Giade","Itas/Gadau","Jama'are","Katagum","Kirfi","Misau","Ningi","Shira","Tafawa Balewa","Toro","Warji","Zaki"],

"Bayelsa": ["Brass","Ekeremor","Kolokuma/Opokuma","Nembe","Ogbia","Sagbama","Southern Ijaw","Yenagoa"],

"Benue": ["Ado","Agatu","Apa","Buruku","Gboko","Guma","Gwer East","Gwer West","Katsina-Ala","Konshisha","Kwande","Logo","Makurdi","Obi","Ogbadibo","Ohimini","Oju","Okpokwu","Otukpo","Tarka","Ukum","Ushongo","Vandeikya"],

"Borno": ["Abadam","Askira/Uba","Bama","Bayo","Biu","Chibok","Damboa","Dikwa","Gubio","Guzamala","Gwoza","Hawul","Jere","Kaga","Kala/Balge","Konduga","Kukawa","Kwaya Kusar","Mafa","Magumeri","Maiduguri","Marte","Mobbar","Monguno","Ngala","Nganzai","Shani"],

"Cross River": ["Abi","Akamkpa","Akpabuyo","Bakassi","Bekwarra","Biase","Boki","Calabar Municipal","Calabar South","Etung","Ikom","Obanliku","Obubra","Obudu","Odukpani","Ogoja","Yakurr","Yala"],

"Delta": ["Aniocha North","Aniocha South","Bomadi","Burutu","Ethiope East","Ethiope West","Ika North East","Ika South","Isoko North","Isoko South","Ndokwa East","Ndokwa West","Okpe","Oshimili North","Oshimili South","Patani","Sapele","Udu","Ughelli North","Ughelli South","Ukwuani","Uvwie","Warri North","Warri South","Warri South West"],

"Ebonyi": ["Abakaliki","Afikpo North","Afikpo South","Ebonyi","Ezza North","Ezza South","Ikwo","Ishielu","Ivo","Izzi","Ohaozara","Ohaukwu","Onicha"],

"Edo": ["Akoko-Edo","Egor","Esan Central","Esan North-East","Esan South-East","Esan West","Etsako Central","Etsako East","Etsako West","Igueben","Ikpoba-Okha","Oredo","Orhionmwon","Ovia North-East","Ovia South-West","Owan East","Owan West","Uhunmwonde"],

"Ekiti": ["Ado Ekiti","Efon","Ekiti East","Ekiti South-West","Ekiti West","Emure","Gbonyin","Ido Osi","Ijero","Ikere","Ikole","Ilejemeje","Irepodun/Ifelodun","Ise/Orun","Moba","Oye"],

"Enugu": ["Aninri","Awgu","Enugu East","Enugu North","Enugu South","Ezeagu","Igbo Etiti","Igbo Eze North","Igbo Eze South","Isi Uzo","Nkanu East","Nkanu West","Nsukka","Oji River","Udenu","Udi","Uzo Uwani"],

"FCT": ["Abaji","Bwari","Gwagwalada","Kuje","Kwali","Municipal Area Council"],

"Gombe": ["Akko","Balanga","Billiri","Dukku","Funakaye","Gombe","Kaltungo","Kwami","Nafada","Shongom","Yamaltu/Deba"],

"Imo": ["Aboh Mbaise","Ahiazu Mbaise","Ehime Mbano","Ezinihitte","Ideato North","Ideato South","Ihitte/Uboma","Ikeduru","Isiala Mbano","Isu","Mbaitoli","Ngor Okpala","Njaba","Nkwerre","Nwangele","Obowo","Oguta","Ohaji/Egbema","Okigwe","Orlu","Orsu","Oru East","Oru West","Owerri Municipal","Owerri North","Owerri West"],

"Jigawa": ["Auyo","Babura","Biriniwa","Birnin Kudu","Buji","Dutse","Gagarawa","Garki","Gumel","Guri","Gwaram","Gwiwa","Hadejia","Jahun","Kafin Hausa","Kaugama","Kazaure","Kiri Kasama","Kiyawa","Maigatari","Malam Madori","Miga","Ringim","Roni","Sule Tankarkar","Taura","Yankwashi"],

"Kaduna": ["Birnin Gwari","Chikun","Giwa","Igabi","Ikara","Jaba","Jema'a","Kachia","Kaduna North","Kaduna South","Kagarko","Kajuru","Kaura","Kauru","Kubau","Kudan","Lere","Makarfi","Sabon Gari","Sanga","Soba","Zangon Kataf","Zaria"],

"Kano": ["Ajingi","Albasu","Bagwai","Bebeji","Bichi","Bunkure","Dala","Dambatta","Dawakin Kudu","Dawakin Tofa","Doguwa","Fagge","Gabasawa","Garko","Garun Mallam","Gaya","Gezawa","Gwale","Gwarzo","Kabo","Kano Municipal","Karaye","Kibiya","Kiru","Kumbotso","Kunchi","Kura","Madobi","Makoda","Minjibir","Nasarawa","Rano","Rimin Gado","Rogo","Shanono","Sumaila","Takai","Tarauni","Tofa","Tsanyawa","Tudun Wada","Ungogo","Warawa","Wudil"],

"Katsina": ["Bakori","Batagarawa","Batsari","Baure","Bindawa","Charanchi","Dan Musa","Dandume","Danja","Daura","Dutsi","Dutsin Ma","Faskari","Funtua","Ingawa","Jibia","Kafur","Kaita","Kankara","Kankia","Katsina","Kurfi","Kusada","Mai'Adua","Malumfashi","Mani","Mashi","Matazu","Musawa","Rimi","Sabuwa","Safana","Sandamu","Zango"],

"Kebbi": ["Aleiro","Arewa Dandi","Argungu","Augie","Bagudo","Birnin Kebbi","Bunza","Dandi","Fakai","Gwandu","Jega","Kalgo","Koko/Besse","Maiyama","Ngaski","Sakaba","Shanga","Suru","Wasagu/Danko","Yauri","Zuru"],

"Kogi": ["Adavi","Ajaokuta","Ankpa","Bassa","Dekina","Ibaji","Idah","Igalamela Odolu","Ijumu","Kabba/Bunu","Kogi","Lokoja","Mopa Muro","Ofu","Ogori/Magongo","Okehi","Okene","Olamaboro","Omala","Yagba East","Yagba West"],

"Kwara": ["Asa","Baruten","Edu","Ekiti","Ifelodun","Ilorin East","Ilorin South","Ilorin West","Irepodun","Isin","Kaiama","Moro","Offa","Oke Ero","Oyun","Pategi"],

"Lagos": ["Agege","Ajeromi-Ifelodun","Alimosho","Amuwo-Odofin","Apapa","Badagry","Epe","Eti-Osa","Ibeju-Lekki","Ifako-Ijaiye","Ikeja","Ikorodu","Kosofe","Lagos Island","Lagos Mainland","Mushin","Ojo","Oshodi-Isolo","Shomolu","Surulere"],

"Nasarawa": ["Akwanga","Awe","Doma","Karu","Keana","Keffi","Kokona","Lafia","Nasarawa","Nasarawa Egon","Obi","Toto","Wamba"],

"Niger": ["Agaie","Agwara","Bida","Borgu","Bosso","Chanchaga","Edati","Gbako","Gurara","Katcha","Kontagora","Lapai","Lavun","Magama","Mariga","Mashegu","Mokwa","Moya","Paikoro","Rafi","Rijau","Shiroro","Suleja","Tafa","Wushishi"],

"Ogun": ["Abeokuta North","Abeokuta South","Ado-Odo/Ota","Ewekoro","Ifo","Ijebu East","Ijebu North","Ijebu North East","Ijebu Ode","Ikenne","Imeko Afon","Ipokia","Obafemi Owode","Odeda","Odogbolu","Ogun Waterside","Remo North","Shagamu"],

"Ondo": ["Akoko North-East","Akoko North-West","Akoko South-East","Akoko South-West","Akure North","Akure South","Ese Odo","Idanre","Ifedore","Ilaje","Ile Oluji/Okeigbo","Irele","Odigbo","Okitipupa","Ondo East","Ondo West","Ose","Owo"],

"Osun": ["Aiyedade","Aiyedire","Atakunmosa East","Atakunmosa West","Boluwaduro","Boripe","Ede North","Ede South","Egbedore","Ejigbo","Ife Central","Ife East","Ife North","Ife South","Ifedayo","Ifelodun","Ila","Ilesa East","Ilesa West","Irepodun","Irewole","Isokan","Iwo","Obokun","Odo Otin","Ola Oluwa","Olorunda","Oriade","Orolu","Osogbo"],

"Oyo": ["Afijio","Akinyele","Atiba","Atisbo","Egbeda","Ibadan North","Ibadan North-East","Ibadan North-West","Ibadan South-East","Ibadan South-West","Ibarapa Central","Ibarapa East","Ibarapa North","Ido","Irepo","Iseyin","Itesiwaju","Iwajowa","Kajola","Lagelu","Ogbomosho North","Ogbomosho South","Ogo Oluwa","Olorunsogo","Oluyole","Ona Ara","Orelope","Ori Ire","Oyo East","Oyo West","Saki East","Saki West","Surulere"],

"Plateau": ["Barkin Ladi","Bassa","Bokkos","Jos East","Jos North","Jos South","Kanam","Kanke","Langtang North","Langtang South","Mangu","Mikang","Pankshin","Qua'an Pan","Riyom","Shendam","Wase"],

"Rivers": ["Abua/Odual","Ahoada East","Ahoada West","Akuku-Toru","Andoni","Asari-Toru","Bonny","Degema","Eleme","Emohua","Etche","Gokana","Ikwerre","Khana","Obio-Akpor","Ogba/Egbema/Ndoni","Ogu/Bolo","Okrika","Omuma","Opobo/Nkoro","Oyigbo","Port Harcourt","Tai"],

"Sokoto": ["Binji","Bodinga","Dange Shuni","Gada","Goronyo","Gudu","Gwadabawa","Illela","Isa","Kebbe","Kware","Rabah","Sabon Birni","Shagari","Silame","Sokoto North","Sokoto South","Tambuwal","Tangaza","Tureta","Wamako","Wurno","Yabo"],

"Taraba": ["Ardo Kola","Bali","Donga","Gashaka","Gassol","Ibi","Jalingo","Karim Lamido","Kurmi","Lau","Sardauna","Takum","Ussa","Wukari","Yorro","Zing"],

"Yobe": ["Bade","Bursari","Damaturu","Fika","Fune","Geidam","Gujba","Gulani","Jakusko","Karasuwa","Machina","Nangere","Nguru","Potiskum","Tarmuwa","Yunusari","Yusufari"],

"Zamfara": ["Anka","Bakura","Birnin Magaji","Bukkuyum","Bungudu","Gummi","Gusau","Kaura Namoda","Maradun","Maru","Shinkafi","Talata Mafara","Tsafe","Zurmi"]

};

    document.getElementById('state').addEventListener('change', function() {
        const lgaSelect = document.getElementById('lga');
        lgaSelect.innerHTML = '<option value="">Select LGA</option>';

        const selectedState = this.value;

        if (lgaData[selectedState]) {
            lgaData[selectedState].forEach(function(lga) {
                const option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                lgaSelect.appendChild(option);
            });
        }
    });
</script>

<script>


@endsection