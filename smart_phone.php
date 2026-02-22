<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

<style>
/* 1. الحاوية */
.iti { width: 100%; display: block; }

/* 2. القائمة الملمومة (كما طلبت سابقاً) */
.iti__country-list {
    position: absolute !important;
    width: 260px !important;
    min-width: 260px !important;
    max-width: 260px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    margin-top: 5px;
    max-height: 250px;
    overflow-y: auto;
    overflow-x: hidden;
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    z-index: 99999 !important;
    direction: rtl;
    text-align: right;
    white-space: normal;
}

.iti__country {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f9fafb;
    box-sizing: border-box;
    width: 100%;
}

.iti__flag-box { margin-left: 10px; flex-shrink: 0; }

.iti__country-name {
    font-family: 'Cairo', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-left: auto;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px; 
}

.iti__dial-code {
    font-size: 11px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 6px;
    direction: ltr;
    flex-shrink: 0;
}

#phone { 
    direction: ltr; 
    padding-right: 90px !important; 
    text-align: left;
    font-weight: 700;
    width: 100%;
    color: #1f2937;
    font-family: monospace; /* خط رقمي واضح */
    letter-spacing: 1px;
}

/* رسالة الخطأ الصارمة */
#error-msg {
    color: #ef4444;
    font-size: 11px;
    font-weight: 800;
    margin-top: 5px;
    text-align: right;
    display: none; /* مخفي افتراضياً */
}
#valid-msg {
    color: #10b981;
    font-size: 11px;
    font-weight: 800;
    margin-top: 5px;
    text-align: right;
    display: none;
}
</style>


<div dir="ltr" class="relative w-full">
    <div class="text-right mb-2">
        <label class="block text-xs font-black text-gray-700">رقم الجوال</label>
    </div>
    
    <input type="tel" id="phone" name="phone" 
           class="w-full p-3.5 bg-gray-50 rounded-xl outline-none border border-gray-200 focus:border-rose-500 focus:bg-white transition-all shadow-sm" 
           required>
    
    <input type="hidden" name="full_phone" id="full_phone">

    <p id="error-msg"></p>
    <p id="valid-msg">الرقم صحيح ومفعل ✓</p>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<script>
(function() {
    const input = document.querySelector("#phone");
    const errorMsg = document.querySelector("#error-msg");
    const validMsg = document.querySelector("#valid-msg");
    const fullPhoneInput = document.querySelector("#full_phone");

    // قائمة الأخطاء حسب معايير جوجل (Libphonenumber)
    const errorMap = [
        "رقم الجوال غير صحيح",     // Invalid number
        "كود الدولة غير صحيح",     // Invalid country code
        "الرقم قصير جداً",         // Too short
        "الرقم طويل جداً",         // Too long
        "رقم الجوال غير صحيح"      // Invalid number (catch-all)
    ];

    const iti = window.intlTelInput(input, {
        onlyCountries: ["sa", "kw", "ae", "qa", "bh", "om", "jo", "eg"],
        initialCountry: "sa",
        separateDialCode: true,
        // تفعيل التنسيق التلقائي الصارم (أهم ميزة)
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        autoPlaceholder: "aggressive", // يظهر لك مثال باهت للرقم الصحيح
        
        localizedCountries: {
            "sa": "السعودية", "ae": "الإمارات", "kw": "الكويت", "qa": "قطر",
            "bh": "البحرين", "om": "عمان", "jo": "الأردن", "eg": "مصر"
        }
    });

    // دالة إعادة تعيين الرسائل
    const reset = function() {
        input.classList.remove("border-rose-500", "border-emerald-500");
        errorMsg.innerHTML = "";
        errorMsg.style.display = "none";
        validMsg.style.display = "none";
    };

    // 1. التنظيف ومنع التكرار (Cleaning)
    input.addEventListener('input', function() {
        let val = input.value;
        // السماح بالأرقام فقط (يمنع الحروف والرموز والنقط)
        if (/[^0-9\s]/.test(val)) {
            input.value = val.replace(/[^0-9\s]/g, '');
        }
        reset();
    });

    // 2. التحقق الصارم عند الخروج من الحقل (Blur)
    input.addEventListener('blur', function() {
        reset();
        let val = input.value.trim();
        
        if (val) {
            if (iti.isValidNumber()) {
                // النجاح
                validMsg.style.display = "block";
                input.classList.add("border-emerald-500");
                fullPhoneInput.value = iti.getNumber();
            } else {
                // الفشل (تحديد السبب بدقة)
                input.classList.add("border-rose-500");
                var errorCode = iti.getValidationError();
                // جلب رسالة الخطأ بالعربي من القائمة
                var msg = errorMap[errorCode] || "تأكد من كتابة الرقم بشكل صحيح";
                
                errorMsg.innerHTML = "⚠️ " + msg;
                errorMsg.style.display = "block";
            }
        }
    });

    // 3. الذكاء الاصطناعي لتغيير الدولة
    input.addEventListener('keyup', function() {
        let val = input.value.replace(/\s/g, ''); // تجاهل المسافات في الفحص
        
        // تنظيف الكود الدولي المكرر (+966)
        let countryData = iti.getSelectedCountryData();
        let dialCode = countryData.dialCode;
        if(val.startsWith('00' + dialCode)) input.value = val.substring(2 + dialCode.length);
        
        // منطق التحويل
        if(val.startsWith('0')) {
            if(val.startsWith('052') || val.startsWith('058')) { setCountry('ae'); return; }
            if(val.startsWith('053') || val.startsWith('057') || val.startsWith('059')) { setCountry('sa'); return; }
            if(val.startsWith('01')) { setCountry('eg'); return; }
            if(val.startsWith('07')) { setCountry('jo'); return; }
            if(val.startsWith('05') && iti.getSelectedCountryData().iso2 !== 'ae') { setCountry('sa'); return; }
        }
        else if(val.startsWith('9')) {
             if(val.startsWith('90') || val.startsWith('91') || val.startsWith('92') || val.startsWith('93')) { setCountry('om'); return; } 
             else { setCountry('kw'); return; } 
        }
        else if(val.startsWith('5') || val.startsWith('6')) {
             if(iti.getSelectedCountryData().iso2 !== 'qa') setCountry('kw');
        }
        else if(val.startsWith('3')) {
            if(iti.getSelectedCountryData().iso2 !== 'qa') setCountry('bh');
        }
        else if(val.startsWith('7')) { setCountry('om'); }
    });

    function setCountry(code) { 
        if (iti.getSelectedCountryData().iso2 !== code) iti.setCountry(code); 
    }
})();
</script>