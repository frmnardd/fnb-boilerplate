/**
 * FnB Boilerplate - Alpine.js Cart & Ordering Engine
 * Manages reactive shopping cart in localStorage, variant selections, and WhatsApp deep-link formatting.
 */

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        isOpen: false,
        orderType: 'dine_in', // 'dine_in' | 'pickup' | 'delivery'
        customer: {
            name: '',
            table: '',
            pickupTime: '',
            phone: '',
            address: '',
            notes: ''
        },

        // Variant Modal State
        variantModal: {
            isOpen: false,
            menu: null,
            selectedChoices: {}, // { 'Ukuran': { name: 'Large', extra_price: 5000 } }
            qty: 1,
            itemNotes: ''
        },

        init() {
            try {
                const saved = localStorage.getItem('fnb_cart_items');
                if (saved) {
                    this.items = JSON.parse(saved);
                }
                const savedCustomer = localStorage.getItem('fnb_customer_info');
                if (savedCustomer) {
                    this.customer = Object.assign(this.customer, JSON.parse(savedCustomer));
                }
            } catch (e) {
                console.error('Failed to load cart from localStorage', e);
                this.items = [];
            }
        },

        save() {
            try {
                localStorage.setItem('fnb_cart_items', JSON.stringify(this.items));
                localStorage.setItem('fnb_customer_info', JSON.stringify(this.customer));
            } catch (e) {
                console.error('Failed to save cart to localStorage', e);
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

        // Open modal to configure menu variants
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

            // If menu has no variants, add directly to cart!
            if (!parsedVariants || !parsedVariants.options || parsedVariants.options.length === 0) {
                this.addItemDirect(menu);
                return;
            }

            // Initialize default selections (first choice of each option group)
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

            const unitPrice = this.getModalUnitPrice();

            this.addItem({
                id: menu.id,
                name: menu.name,
                image_url: menu.image_url,
                base_price: menu.price,
                unit_price: unitPrice,
                variants: selectedList,
                notes: this.variantModal.itemNotes.trim(),
                qty: this.variantModal.qty || 1
            });

            this.closeVariantModal();
            this.openDrawer();
        },

        addItemDirect(menu) {
            this.addItem({
                id: menu.id,
                name: menu.name,
                image_url: menu.image_url,
                base_price: menu.price,
                unit_price: parseInt(menu.price, 10),
                variants: [],
                notes: '',
                qty: 1
            });
            this.openDrawer();
        },

        addItem(newItem) {
            // Check if exact same item with same variants and notes exists
            const existingIndex = this.items.findIndex(item => {
                return item.id === newItem.id &&
                       JSON.stringify(item.variants) === JSON.stringify(newItem.variants) &&
                       item.notes === newItem.notes;
            });

            if (existingIndex > -1) {
                this.items[existingIndex].qty += newItem.qty;
            } else {
                this.items.push(newItem);
            }

            this.save();
        },

        updateQty(index, delta) {
            if (this.items[index]) {
                this.items[index].qty += delta;
                if (this.items[index].qty <= 0) {
                    this.removeItem(index);
                    return;
                }
                this.save();
            }
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.save();
        },

        clearCart() {
            this.items = [];
            this.save();
        },

        getTotalCount() {
            return this.items.reduce((sum, item) => sum + item.qty, 0);
        },

        getSubtotal() {
            return this.items.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
        },

        formatRupiah(amount) {
            return 'Rp ' + (amount || 0).toLocaleString('id-ID');
        },

        // Build and trigger the WhatsApp checkout link
        sendWhatsAppOrder(whatsappNumber, siteName = 'Kopi & Rasa Nusantara') {
            if (this.items.length === 0) {
                alert('Keranjang belanja Anda masih kosong.');
                return;
            }

            // Customer details validation based on order type
            if (!this.customer.name.trim()) {
                alert('Silakan masukkan nama pemesan.');
                return;
            }

            let typeLabel = '';
            let detailsText = '';

            if (this.orderType === 'dine_in') {
                typeLabel = 'Dine-In (Makan di Tempat)';
                if (!this.customer.table.trim()) {
                    alert('Silakan masukkan nomor meja untuk Dine-In.');
                    return;
                }
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Nomor Meja: Meja ${this.customer.table.trim()}`;
            } else if (this.orderType === 'pickup') {
                typeLabel = 'Takeaway / Pickup Sendiri';
                const timeText = this.customer.pickupTime.trim() || 'Secepatnya';
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Estimasi Jam Ambil: ${timeText}`;
                if (this.customer.phone.trim()) {
                    detailsText += `\n- No WhatsApp: ${this.customer.phone.trim()}`;
                }
            } else if (this.orderType === 'delivery') {
                typeLabel = 'Delivery (Pengantaran)';
                if (!this.customer.address.trim()) {
                    alert('Silakan masukkan alamat lengkap pengantaran.');
                    return;
                }
                detailsText = `- Nama: ${this.customer.name.trim()}\n- Alamat Antar: ${this.customer.address.trim()}`;
                if (this.customer.phone.trim()) {
                    detailsText += `\n- No WhatsApp: ${this.customer.phone.trim()}`;
                }
                if (this.customer.notes.trim()) {
                    detailsText += `\n- Catatan Kurir: ${this.customer.notes.trim()}`;
                }
            }

            // Format items list
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

            const totalText = this.formatRupiah(this.getSubtotal());

            // Construct full WhatsApp message
            const message = 
`Halo *${siteName}*, saya ingin memesan via website:

━━━━━━━━━━━━━━━━━━━━
*DAFTAR PESANAN:*
${itemsText.trim()}
━━━━━━━━━━━━━━━━━━━━
*TOTAL BAYAR: ${totalText}*
*TIPE PESANAN: ${typeLabel}*

*DETAIL PEMESAN:*
${detailsText}

Mohon konfirmasi ketersediaan pesanan dan rincian pembayarannya. Terima kasih! 🙏`;

            this.save();

            // Format clean phone number
            let cleanPhone = String(whatsappNumber).replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }

            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
        }
    });

    // Franchise ROI Calculator Store / Component
    Alpine.data('roiCalculator', () => ({
        // Sliders & Input values
        dailyCups: 85,          // 85 cup/porsi per hari
        avgPricePerItem: 22000, // Rp 22.000 rata-rata harga jual
        cogsPercentage: 35,     // HPP (COGS) 35%
        monthlyRent: 3500000,   // Biaya sewa tempat bulanan (Rp 3.5jt)
        monthlyLabor: 4000000,  // Gaji 2 staf (Rp 4jt)
        monthlyUtility: 1000000,// Listrik, air, internet (Rp 1jt)
        investmentPackage: 45000000, // Modal awal paket booth/outlet (Rp 45jt)

        packages: [
            { name: 'Paket Booth Express', investment: 35000000, desc: 'Cocok untuk teras minimarket & cloud kitchen' },
            { name: 'Paket Coffee Shop Standard', investment: 65000000, desc: 'Dine-in compact kapasitas 15-20 kursi' },
            { name: 'Paket Flagship Bistro', investment: 120000000, desc: 'Full resto & bar dengan fasilitas dine-in premium' }
        ],

        setPackage(amount) {
            this.investmentPackage = amount;
        },

        // Computed metrics
        get monthlyRevenue() {
            return this.dailyCups * this.avgPricePerItem * 30;
        },

        get monthlyGrossProfit() {
            return this.monthlyRevenue * ((100 - this.cogsPercentage) / 100);
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
- Estimasi Omset: ${this.formatRupiah(this.monthlyRevenue)} /bulan
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
