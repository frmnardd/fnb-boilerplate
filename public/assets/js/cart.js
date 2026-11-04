/**
 * FnB Boilerplate - Alpine.js Cart & Omnichannel Ordering Engine (PRD v2.0)
 * Multi-Outlet Routing, Instant QRIS Dual Checkout, Voucher Engine, & Dynamic Modifiers.
 */

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        isOpen: false,
        orderType: 'dine_in', // 'dine_in' | 'pickup' | 'delivery'
        customer: {
            name: '',
            table: '',
            table_number: '',
            pickupTime: '',
            pickup_time: '',
            phone: '',
            address: '',
            notes: ''
        },

        // Outlets Data (Senopati, Braga, Malioboro)
        outlets: [
            {
                id: 1,
                slug: 'senopati',
                name: 'Cabang Senopati (Flagship Store)',
                city: 'Jakarta Selatan',
                address: 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
                whatsapp: '6281211112222',
                hours: '08:00 - 23:00 WIB'
            },
            {
                id: 2,
                slug: 'braga',
                name: 'Cabang Braga Heritage',
                city: 'Bandung',
                address: 'Jl. Braga No. 88, Sumur Bandung, Kota Bandung',
                whatsapp: '6281233334444',
                hours: '07:30 - 22:30 WIB'
            },
            {
                id: 3,
                slug: 'malioboro',
                name: 'Cabang Malioboro Heritage',
                city: 'Yogyakarta',
                address: 'Jl. Malioboro No. 120, Gedong Tengen, Kota Yogyakarta',
                whatsapp: '6281255556666',
                hours: '08:00 - 23:30 WIB'
            }
        ],
        activeOutlet: null,

        // Voucher State (FR-04)
        voucherCodeInput: '',
        appliedVoucher: null,
        voucherError: '',
        voucherSuccess: '',

        // Variant Modal State (FR-03)
        variantModal: {
            isOpen: false,
            menu: null,
            selectedChoices: {},
            qty: 1,
            itemNotes: ''
        },

        // Product Detail Modal State (FR-06)
        productDetailModal: {
            isOpen: false,
            menu: null
        },

        // QRIS Payment Modal State (FR-07)
        qrisModal: {
            isOpen: false,
            loading: false,
            orderId: null,
            orderNumber: '',
            orderToken: '',
            grandTotal: 0,
            countdown: 900, // 15 mins
            timer: null,
            status: 'PENDING'
        },

        init() {
            // Default active outlet
            this.activeOutlet = this.outlets[0];

            try {
                const saved = localStorage.getItem('fnb_cart_items');
                if (saved) {
                    this.items = JSON.parse(saved);
                    // Ensure each item has calculated properties (prevent Rp 0 bug)
                    this.items.forEach(item => {
                        item.qty = item.qty || item.quantity || 1;
                        item.quantity = item.qty;
                        item.unit_price = item.unit_price || item.base_price || 0;
                        item.calculated_price = item.unit_price * item.qty;
                    });
                }

                const savedCustomer = localStorage.getItem('fnb_customer_info');
                if (savedCustomer) {
                    this.customer = Object.assign(this.customer, JSON.parse(savedCustomer));
                    if (this.customer.table_number && !this.customer.table) {
                        this.customer.table = this.customer.table_number;
                    }
                    if (this.customer.table && !this.customer.table_number) {
                        this.customer.table_number = this.customer.table;
                    }
                }

                const savedOutlet = localStorage.getItem('fnb_active_outlet');
                if (savedOutlet) {
                    const parsed = JSON.parse(savedOutlet);
                    const found = this.outlets.find(o => o.slug === parsed.slug || o.id === parsed.id);
                    if (found) this.activeOutlet = found;
                }

                const savedVoucher = localStorage.getItem('fnb_active_voucher');
                if (savedVoucher) {
                    this.appliedVoucher = JSON.parse(savedVoucher);
                }
            } catch (e) {
                console.error('Failed to load cart from localStorage', e);
                this.items = [];
            }

            // Check URL query parameters (e.g. ?outlet=senopati&table=08)
            try {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('outlet')) {
                    const slug = urlParams.get('outlet').toLowerCase();
                    const outlet = this.outlets.find(o => o.slug === slug || String(o.id) === slug);
                    if (outlet) {
                        this.setActiveOutlet(outlet.slug);
                    }
                }
                if (urlParams.has('table')) {
                    const tableNum = urlParams.get('table').trim();
                    this.customer.table = tableNum;
                    this.customer.table_number = tableNum;
                    this.orderType = 'dine_in';
                }
            } catch (e) {}
        },

        save() {
            try {
                this.customer.table_number = this.customer.table || this.customer.table_number;
                this.customer.table = this.customer.table_number;
                localStorage.setItem('fnb_cart_items', JSON.stringify(this.items));
                localStorage.setItem('fnb_customer_info', JSON.stringify(this.customer));
                localStorage.setItem('fnb_active_outlet', JSON.stringify(this.activeOutlet));
                if (this.appliedVoucher) {
                    localStorage.setItem('fnb_active_voucher', JSON.stringify(this.appliedVoucher));
                } else {
                    localStorage.removeItem('fnb_active_voucher');
                }
            } catch (e) {
                console.error('Failed to save cart to localStorage', e);
            }
        },

        setActiveOutlet(slugOrId) {
            const found = this.outlets.find(o => o.slug === slugOrId || o.id === parseInt(slugOrId, 10));
            if (found) {
                this.activeOutlet = found;
                this.save();
            }
        },

        openDrawer() {
            this.isOpen = true;
        },

        closeDrawer() {
            this.isOpen = false;
        },

        toggleDrawer() {
            this.isOpen = !this.isOpen;
        },

        // --- FR-06: Product Detail Modal ---
        openProductDetail(menu) {
            this.productDetailModal.menu = menu;
            this.productDetailModal.isOpen = true;
        },

        closeProductDetail() {
            this.productDetailModal.isOpen = false;
            this.productDetailModal.menu = null;
        },

        // --- FR-03: Dynamic Category Variant Modal ---
        openVariantModal(menu) {
            if (!menu.is_available) return;

            let parsedVariants = null;
            if (menu.variants_json) {
                try {
                    parsedVariants = typeof menu.variants_json === 'string'
                        ? JSON.parse(menu.variants_json)
                        : menu.variants_json;
                } catch (e) {
                    parsedVariants = null;
                }
            }

            if (!parsedVariants || !parsedVariants.options || parsedVariants.options.length === 0) {
                this.addItemDirect(menu);
                return;
            }

            const defaults = {};
            parsedVariants.options.forEach(opt => {
                if (opt.choices && opt.choices.length > 0) {
                    defaults[opt.name] = opt.choices[0];
                }
            });

            this.variantModal.menu = menu;
            this.variantModal.menu.parsedVariants = parsedVariants;
            this.variantModal.selectedChoices = defaults;
            this.variantModal.qty = 1;
            this.variantModal.itemNotes = '';
            this.variantModal.isOpen = true;
        },

        closeVariantModal() {
            this.variantModal.isOpen = false;
            this.variantModal.menu = null;
        },

        selectVariantChoice(groupName, choice) {
            this.variantModal.selectedChoices[groupName] = choice;
        },

        getModalUnitPrice() {
            if (!this.variantModal.menu) return 0;
            let price = parseInt(this.variantModal.menu.price, 10) || 0;
            Object.values(this.variantModal.selectedChoices).forEach(choice => {
                if (choice && choice.extra_price) {
                    price += parseInt(choice.extra_price, 10);
                }
            });
            return price;
        },

        getModalTotalPrice() {
            return this.getModalUnitPrice() * (this.variantModal.qty || 1);
        },

        confirmVariantSelection() {
            const menu = this.variantModal.menu;
            if (!menu) return;

            const selectedList = Object.entries(this.variantModal.selectedChoices).map(([group, choice]) => {
                return {
                    group: group,
                    name: choice.name,
                    extra_price: choice.extra_price || 0
                };
            });

            const selectedDict = {};
            selectedList.forEach(item => {
                selectedDict[item.group] = item.name;
            });

            const unitPrice = this.getModalUnitPrice();
            const qty = this.variantModal.qty || 1;

            this.addItem({
                id: menu.id,
                name: menu.name,
                image_url: menu.image_url,
                category_id: menu.category_id || 1,
                station: menu.station || 'BAR',
                base_price: parseInt(menu.price, 10),
                unit_price: unitPrice,
                calculated_price: unitPrice * qty,
                variants: selectedList,
                selected_variants: selectedDict,
                notes: this.variantModal.itemNotes.trim(),
                qty: qty,
                quantity: qty
            });

            this.closeVariantModal();
            this.openDrawer();
        },

        addItemDirect(menu) {
            const unitPrice = parseInt(menu.price, 10);
            this.addItem({
                id: menu.id,
                name: menu.name,
                image_url: menu.image_url,
                category_id: menu.category_id || 1,
                station: menu.station || 'BAR',
                base_price: unitPrice,
                unit_price: unitPrice,
                calculated_price: unitPrice,
                variants: [],
                selected_variants: {},
                notes: '',
                qty: 1,
                quantity: 1
            });
            this.openDrawer();
        },

        addItem(newItem) {
            const existingIndex = this.items.findIndex(item => {
                return item.id === newItem.id &&
                       JSON.stringify(item.variants) === JSON.stringify(newItem.variants) &&
                       item.notes === newItem.notes;
            });

            if (existingIndex > -1) {
                this.items[existingIndex].qty += newItem.qty;
                this.items[existingIndex].quantity = this.items[existingIndex].qty;
                this.items[existingIndex].calculated_price = this.items[existingIndex].unit_price * this.items[existingIndex].qty;
            } else {
                newItem.cart_item_id = 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
                newItem.quantity = newItem.qty;
                newItem.calculated_price = newItem.unit_price * newItem.qty;
                this.items.push(newItem);
            }

            this.save();
            this.recalculateVoucher();
        },

        removeItem(cartItemId) {
            this.items = this.items.filter(item => (item.cart_item_id || item.id) !== cartItemId);
            this.save();
            this.recalculateVoucher();
        },

        incrementQuantity(cartItemId) {
            const item = this.items.find(i => (i.cart_item_id || i.id) === cartItemId);
            if (item) {
                item.qty++;
                item.quantity = item.qty;
                item.calculated_price = item.unit_price * item.qty;
                this.save();
                this.recalculateVoucher();
            }
        },

        decrementQuantity(cartItemId) {
            const item = this.items.find(i => (i.cart_item_id || i.id) === cartItemId);
            if (item) {
                if (item.qty > 1) {
                    item.qty--;
                    item.quantity = item.qty;
                    item.calculated_price = item.unit_price * item.qty;
                } else {
                    this.removeItem(cartItemId);
                    return;
                }
                this.save();
                this.recalculateVoucher();
            }
        },

        clearCart() {
            this.items = [];
            this.appliedVoucher = null;
            this.save();
        },

        getTotalCount() {
            return this.items.reduce((sum, item) => sum + (item.qty || 1), 0);
        },

        getSubtotal() {
            return this.items.reduce((sum, item) => sum + ((item.unit_price || item.base_price || 0) * (item.qty || 1)), 0);
        },

        // --- FR-04: Interactive Voucher Promo Engine ---
        applyVoucher(rawCode) {
            this.voucherError = '';
            this.voucherSuccess = '';
            const code = (rawCode || this.voucherCodeInput || '').trim().toUpperCase();

            if (!code) {
                this.voucherError = 'Silakan masukkan kode voucher.';
                return;
            }

            if (this.items.length === 0) {
                this.voucherError = 'Keranjang masih kosong, tambahkan menu dahulu.';
                return;
            }

            const subtotal = this.getSubtotal();

            if (code === 'KOPIASIK') {
                // 20% discount specifically on Coffee / Kopi items
                let coffeeSubtotal = 0;
                this.items.forEach(item => {
                    const name = (item.name || '').toLowerCase();
                    const isCoffee = item.category_id === 1 || name.includes('kopi') || name.includes('brew') || item.station === 'BAR';
                    if (isCoffee) {
                        coffeeSubtotal += (item.unit_price * item.qty);
                    }
                });

                if (coffeeSubtotal === 0) {
                    this.voucherError = 'Voucher KOPIASIK hanya berlaku untuk menu Kopi Susu / Kopi Nusantara.';
                    return;
                }

                const discount = Math.round(coffeeSubtotal * 0.20);
                this.appliedVoucher = {
                    code: 'KOPIASIK',
                    title: 'Diskon 20% Kopi Susu',
                    discount: discount,
                    type: 'PERCENT',
                    value: 20
                };
                this.voucherSuccess = `Voucher KOPIASIK berhasil diterapkan! Hemat ${this.formatRupiah(discount)}.`;
                this.voucherCodeInput = '';
                this.save();
            } else if (code === 'KENYANG2') {
                // Rp 15.000 discount with minimum subtotal Rp 60.000
                if (subtotal < 60000) {
                    this.voucherError = `Voucher KENYANG2 memerlukan minimum belanja Rp 60.000 (Kurang ${this.formatRupiah(60000 - subtotal)}).`;
                    return;
                }

                const discount = 15000;
                this.appliedVoucher = {
                    code: 'KENYANG2',
                    title: 'Hemat Rp 15.000 Kenyang',
                    discount: discount,
                    type: 'FLAT',
                    value: 15000
                };
                this.voucherSuccess = `Voucher KENYANG2 berhasil diterapkan! Hemat ${this.formatRupiah(discount)}.`;
                this.voucherCodeInput = '';
                this.save();
            } else {
                this.voucherError = 'Kode voucher tidak valid atau telah berakhir.';
            }
        },

        removeVoucher() {
            this.appliedVoucher = null;
            this.voucherError = '';
            this.voucherSuccess = 'Voucher berhasil dilepas.';
            this.save();
        },

        recalculateVoucher() {
            if (!this.appliedVoucher) return;
            const code = this.appliedVoucher.code;
            const subtotal = this.getSubtotal();

            if (code === 'KOPIASIK') {
                let coffeeSubtotal = 0;
                this.items.forEach(item => {
                    const name = (item.name || '').toLowerCase();
                    const isCoffee = item.category_id === 1 || name.includes('kopi') || name.includes('brew') || item.station === 'BAR';
                    if (isCoffee) {
                        coffeeSubtotal += (item.unit_price * item.qty);
                    }
                });
                if (coffeeSubtotal === 0) {
                    this.appliedVoucher = null;
                } else {
                    this.appliedVoucher.discount = Math.round(coffeeSubtotal * 0.20);
                }
            } else if (code === 'KENYANG2') {
                if (subtotal < 60000) {
                    this.appliedVoucher = null;
                } else {
                    this.appliedVoucher.discount = 15000;
                }
            }
            this.save();
        },

        copyAndApplyVoucher(code) {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code);
                }
            } catch (e) {}

            this.voucherCodeInput = code;
            this.applyVoucher(code);
            this.openDrawer();
        },

        getDiscount() {
            return this.appliedVoucher ? (this.appliedVoucher.discount || 0) : 0;
        },

        getDeliveryFee() {
            return this.orderType === 'delivery' ? 10000 : 0;
        },

        getGrandTotal() {
            const sub = this.getSubtotal();
            const disc = this.getDiscount();
            const del = this.getDeliveryFee();
            return Math.max(0, sub - disc + del);
        },

        formatRupiah(amount) {
            return 'Rp ' + Math.round(amount || 0).toLocaleString('id-ID');
        },

        // --- FR-07: Dual Checkout - Fast QRIS Checkout ---
        async initiateQrisCheckout() {
            if (this.items.length === 0) {
                alert('Keranjang belanja Anda masih kosong.');
                return;
            }
            if (!this.customer.name.trim()) {
                alert('Silakan masukkan nama pemesan.');
                return;
            }
            if (this.orderType === 'dine_in' && !((this.customer.table || this.customer.table_number || '').trim())) {
                alert('Silakan masukkan nomor meja untuk Dine-In.');
                return;
            }
            if (this.orderType === 'delivery' && !this.customer.address.trim()) {
                alert('Silakan masukkan alamat lengkap pengantaran.');
                return;
            }

            this.qrisModal.loading = true;

            const payload = {
                order_type: this.orderType,
                outlet_id: this.activeOutlet ? this.activeOutlet.id : 1,
                outlet_name: this.activeOutlet ? this.activeOutlet.name : 'Cabang Senopati',
                customer_name: this.customer.name.trim(),
                customer_phone: this.customer.phone.trim(),
                table_number: this.customer.table || this.customer.table_number || '',
                pickup_time: this.customer.pickupTime || this.customer.pickup_time || '',
                delivery_address: this.customer.address.trim(),
                delivery_fee: this.getDeliveryFee(),
                voucher_code: this.appliedVoucher ? this.appliedVoucher.code : '',
                voucher_discount: this.getDiscount(),
                subtotal: this.getSubtotal(),
                grand_total: this.getGrandTotal(),
                payment_method: 'QRIS',
                items: this.items
            };

            try {
                const response = await fetch('/api/orders/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();

                if (res.success) {
                    this.qrisModal.orderId = res.order_id;
                    this.qrisModal.orderNumber = res.order_number;
                    this.qrisModal.orderToken = res.order_token;
                    this.qrisModal.grandTotal = res.grand_total;
                    this.qrisModal.status = 'PENDING';
                    this.qrisModal.countdown = 900;
                    this.qrisModal.isOpen = true;
                    this.closeDrawer();

                    // Start 15-minute countdown timer
                    if (this.qrisModal.timer) clearInterval(this.qrisModal.timer);
                    this.qrisModal.timer = setInterval(() => {
                        if (this.qrisModal.countdown > 0) {
                            this.qrisModal.countdown--;
                        } else {
                            clearInterval(this.qrisModal.timer);
                        }
                    }, 1000);

                    // Render Dynamic QRIS code via QRCode.js
                    setTimeout(() => {
                        const qrContainer = document.getElementById('qris-modal-qrcode');
                        if (qrContainer && typeof QRCode !== 'undefined') {
                            qrContainer.innerHTML = '';
                            const qrisPayload = `00020101021226580016ID.CO.QRIS.WWW0118936009980000000000520458125303360540${res.grand_total}5802ID5913FNB TECH RES6007JAKARTA62070703A016304${res.order_number}`;
                            new QRCode(qrContainer, {
                                text: qrisPayload,
                                width: 200,
                                height: 200,
                                colorDark: '#0f172a',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.M
                            });
                        }
                    }, 150);
                } else {
                    alert('Gagal membuat pesanan QRIS: ' + (res.message || 'Silakan coba lagi'));
                }
            } catch (err) {
                console.error(err);
                alert('Koneksi terputus saat menghubungi server.');
            } finally {
                this.qrisModal.loading = false;
            }
        },

        async simulateQrisPayment() {
            if (!this.qrisModal.orderToken) return;
            this.qrisModal.loading = true;

            try {
                const response = await fetch('/api/orders/simulate-pay', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_token: this.qrisModal.orderToken })
                });
                const res = await response.json();
                if (res.success) {
                    if (this.qrisModal.timer) clearInterval(this.qrisModal.timer);
                    this.clearCart();
                    window.location.href = `/order/${this.qrisModal.orderToken}`;
                } else {
                    alert('Simulasi gagal: ' + (res.message || 'Error'));
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi.');
            } finally {
                this.qrisModal.loading = false;
            }
        },

        // --- FR-07: Dual Checkout - WhatsApp Branch Order ---
        sendWhatsAppOrder(fallbackNumber, siteName = 'Kopi & Rasa Nusantara') {
            if (this.items.length === 0) {
                alert('Keranjang belanja Anda masih kosong.');
                return;
            }

            if (!this.customer.name.trim()) {
                alert('Silakan masukkan nama pemesan.');
                return;
            }

            let typeLabel = '';
            let detailsText = '';

            if (this.orderType === 'dine_in') {
                typeLabel = 'Dine-In (Makan di Meja)';
                const tbl = (this.customer.table || this.customer.table_number || '').trim();
                if (!tbl) {
                    alert('Silakan masukkan nomor meja untuk Dine-In.');
                    return;
                }
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Meja: No. ${tbl}`;
            } else if (this.orderType === 'pickup') {
                typeLabel = 'Takeaway / Pickup Sendiri';
                const timeText = (this.customer.pickupTime || this.customer.pickup_time || '').trim() || 'Secepatnya';
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Estimasi Jam Ambil: ${timeText}`;
                if (this.customer.phone.trim()) {
                    detailsText += `\n- No WhatsApp: ${this.customer.phone.trim()}`;
                }
            } else if (this.orderType === 'delivery') {
                typeLabel = 'Delivery (Pesan Antar)';
                if (!this.customer.address.trim()) {
                    alert('Silakan masukkan alamat lengkap pengantaran.');
                    return;
                }
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Alamat Antar: ${this.customer.address.trim()}`;
                if (this.customer.phone.trim()) {
                    detailsText += `\n- No WhatsApp: ${this.customer.phone.trim()}`;
                }
                if (this.customer.notes.trim()) {
                    detailsText += `\n- Catatan Alamat: ${this.customer.notes.trim()}`;
                }
            }

            let itemsText = '';
            this.items.forEach((item, idx) => {
                let variantStr = '';
                if (item.variants && item.variants.length > 0) {
                    variantStr = ' (' + item.variants.map(v => v.name).join(', ') + ')';
                }
                itemsText += `${idx + 1}. *${item.name}${variantStr}*\n`;
                itemsText += `   ${item.qty}x @ ${this.formatRupiah(item.unit_price)} = *${this.formatRupiah(item.unit_price * item.qty)}*\n`;
                if (item.notes) {
                    itemsText += `   _Catatan: ${item.notes}_\n`;
                }
                itemsText += '\n';
            });

            const subtotalText = this.formatRupiah(this.getSubtotal());
            const discountText = this.getDiscount() > 0 ? `\n*DISKON VOUCHER (${this.appliedVoucher.code}): -${this.formatRupiah(this.getDiscount())}*` : '';
            const deliveryText = this.getDeliveryFee() > 0 ? `\n*ONGKOS KIRIM: ${this.formatRupiah(this.getDeliveryFee())}*` : '';
            const totalText = this.formatRupiah(this.getGrandTotal());

            const branchName = this.activeOutlet ? this.activeOutlet.name : siteName;

            const message = 
`Halo *${branchName}*, saya ingin memesan via website:

----------------------------------------
*DAFTAR PESANAN:*
${itemsText.trim()}
----------------------------------------
Subtotal: ${subtotalText}${discountText}${deliveryText}
*TOTAL AKHIR: ${totalText}*
*TIPE PESANAN: ${typeLabel}*

*DETAIL PEMESAN:*
${detailsText}

Mohon konfirmasi ketersediaan pesanan dan rincian pembayarannya. Terima kasih!`;

            this.save();

            const targetWa = (this.activeOutlet && this.activeOutlet.whatsapp) ? this.activeOutlet.whatsapp : fallbackNumber;
            let cleanPhone = String(targetWa).replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }

            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
        }
    });

    // --- FR-09: Franchise ROI Simulator Component 2.0 ---
    Alpine.data('roiCalculator', () => ({
        dailyCups: 85,
        avgPricePerItem: 22000,
        cogsPercentage: 35, // HPP 35%
        monthlyRent: 3500000,
        monthlyLabor: 4000000,
        monthlyUtility: 1000000,
        investmentPackage: 65000000,
        showOpexBreakdown: false,

        packages: [
            { name: 'Paket Booth Express', investment: 35000000, desc: 'Cocok untuk teras minimarket, stasiun, & cloud kitchen' },
            { name: 'Paket Coffee Shop Standard', investment: 65000000, desc: 'Dine-in compact kapasitas 15-20 kursi pelanggan' },
            { name: 'Paket Flagship Bistro', investment: 120000000, desc: 'Full resto & coffee bar fasilitas dine-in premium' }
        ],

        setPackage(amount) {
            this.investmentPackage = amount;
        },

        get monthlyRevenue() {
            return this.dailyCups * this.avgPricePerItem * 30;
        },

        get monthlyCogs() {
            return this.monthlyRevenue * (this.cogsPercentage / 100);
        },

        get monthlyGrossProfit() {
            return this.monthlyRevenue - this.monthlyCogs;
        },

        get monthlyOpex() {
            return parseInt(this.monthlyRent, 10) + parseInt(this.monthlyLabor, 10) + parseInt(this.monthlyUtility, 10);
        },

        get monthlyNetProfit() {
            const net = this.monthlyGrossProfit - this.monthlyOpex;
            return Math.max(0, net);
        },

        get paybackPeriodMonths() {
            if (this.monthlyNetProfit <= 0) return 99;
            const months = this.investmentPackage / this.monthlyNetProfit;
            return Math.max(1, Math.round(months * 10) / 10);
        },

        get annualROI() {
            if (this.investmentPackage <= 0) return 0;
            const annualProfit = this.monthlyNetProfit * 12;
            return Math.round((annualProfit / this.investmentPackage) * 100);
        },

        formatRupiah(amount) {
            return 'Rp ' + Math.round(amount || 0).toLocaleString('id-ID');
        },

        generateFranchiseWhatsAppUrl(whatsappFranchiseNumber) {
            const msg = 
`Halo Tim Kemitraan Kopi & Rasa Nusantara,
Saya tertarik bermitra dan telah melakukan simulasi perhitungan ROI di website:

- Target Penjualan: ${this.dailyCups} cup/hari
- Estimasi Omzet: ${this.formatRupiah(this.monthlyRevenue)} /bulan
- Estimasi HPP Bahan Baku: ${this.formatRupiah(this.monthlyCogs)} (~${this.cogsPercentage}%)
- Estimasi Biaya Operasional: ${this.formatRupiah(this.monthlyOpex)} /bulan
- Estimasi Laba Bersih: ${this.formatRupiah(this.monthlyNetProfit)} /bulan
- Paket Minat: ${this.formatRupiah(this.investmentPackage)}
- Proyeksi Balik Modal: ~${this.paybackPeriodMonths} Bulan (ROI ${this.annualROI}%/tahun)

Mohon informasi ketersediaan slot kota, proposal kemitraan resmi, dan jadwal diskusi lanjutan. Terima kasih!`;

            let cleanPhone = String(whatsappFranchiseNumber).replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }

            return `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`;
        }
    }));
});
