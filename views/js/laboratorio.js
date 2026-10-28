document.addEventListener('DOMContentLoaded', () => {
    const stage = document.getElementById('lab-stage');
    const canvas = document.getElementById('lab-canvas');
    const componentList = document.getElementById('component-list');
    const componentSearch = document.getElementById('component-search');
    const result = document.getElementById('diagnostic-result');
    const componentInfo = document.getElementById('component-info');
    const zoomLevel = document.getElementById('zoom-level');
    const helpModal = document.getElementById('help-modal');

    const imageBase = '../../assets/icons-perifericos/';
    const components = [
        { id: 'amd-athlon-xp-2500-plus', type: 'cpu', name: 'AMD Athlon XP 2500+', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Athlon XP', socket: 'Socket A', compatibleSockets: ['Socket A'], tdpWatts: 68, cores: 1, threads: 1, image: 'CPU.png', detail: 'AMD | Athlon XP | Socket A | 1 núcleo | 1 thread.' },
        { id: 'amd-athlon-64-3200-plus', type: 'cpu', name: 'AMD Athlon 64 3200+', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Athlon 64', cores: 1, threads: 1, image: 'CPU.png', detail: 'AMD | Athlon 64 | 1 núcleo | 1 thread.' },
        { id: 'amd-phenom-ii-x4-955', type: 'cpu', name: 'AMD Phenom II X4 955', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Phenom II', cores: 4, threads: 4, image: 'CPU.png', detail: 'AMD | Phenom II | 4 núcleos | 4 threads.' },
        { id: 'amd-fx-6300', type: 'cpu', name: 'AMD FX-6300', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'FX', cores: 6, threads: 6, image: 'CPU.png', detail: 'AMD | FX | 6 núcleos | 6 threads.' },
        { id: 'amd-a8-7600', type: 'cpu', name: 'AMD A8-7600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'A-Series', cores: 4, threads: 4, image: 'CPU.png', detail: 'AMD | A-Series | 4 núcleos | 4 threads.' },
        { id: 'amd-ryzen-3-1200', type: 'cpu', name: 'AMD Ryzen 3 1200', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 1000', cores: 4, threads: 4, image: 'CPU.png', detail: 'AMD | Ryzen 1000 | 4 núcleos | 4 threads.' },
        { id: 'amd-ryzen-5-1600', type: 'cpu', name: 'AMD Ryzen 5 1600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 1000', cores: 6, threads: 12, image: 'CPU.png', detail: 'AMD | Ryzen 1000 | 6 núcleos | 12 threads.' },
        { id: 'amd-ryzen-5-2600', type: 'cpu', name: 'AMD Ryzen 5 2600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 2000', cores: 6, threads: 12, image: 'CPU.png', detail: 'AMD | Ryzen 2000 | 6 núcleos | 12 threads.' },
        { id: 'amd-ryzen-5-3600', type: 'cpu', name: 'AMD Ryzen 5 3600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 3000', cores: 6, threads: 12, image: 'CPU.png', detail: 'AMD | Ryzen 3000 | 6 núcleos | 12 threads.' },
        { id: 'amd-ryzen-5-5600', type: 'cpu', name: 'AMD Ryzen 5 5600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 5000', socket: 'AM4', compatibleSockets: ['AM4'], tdpWatts: 65, cores: 6, threads: 12, image: 'CPU.png', detail: 'AMD | Ryzen 5000 | AM4 | 6 núcleos | 12 threads.' },
        { id: 'amd-ryzen-5-7600', type: 'cpu', name: 'AMD Ryzen 5 7600', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 7000', cores: 6, threads: 12, image: 'CPU.png', detail: 'AMD | Ryzen 7000 | 6 núcleos | 12 threads.' },
        { id: 'amd-ryzen-7-9800x3d', type: 'cpu', name: 'AMD Ryzen 7 9800X3D', manufacturer: 'AMD', category: 'Processadores', subcategory: 'CPU AMD', generation: 'Ryzen 9000', socket: 'AM5', compatibleSockets: ['AM5'], tdpWatts: 120, cores: 8, threads: 16, image: 'CPU.png', detail: 'AMD | Ryzen 9000 | AM5 | 8 núcleos | 16 threads.' },
        { id: 'intel-core-i5-12400f', type: 'cpu', name: 'Intel Core i5-12400F', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '12ª geração', cores: 6, threads: 12, image: 'CPU.png', detail: 'Intel | 12ª geração | 6 núcleos | 12 threads.' },
        { id: 'intel-core-i5-2500', type: 'cpu', name: 'Intel Core i5-2500', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '2ª geração', cores: 4, threads: 4, image: 'CPU.png', detail: 'Intel | 2ª geração | 4 núcleos | 4 threads.' },
        { id: 'intel-core-i7-3770', type: 'cpu', name: 'Intel Core i7-3770', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '3ª geração', cores: 4, threads: 8, image: 'CPU.png', detail: 'Intel | 3ª geração | 4 núcleos | 8 threads.' },
        { id: 'intel-core-i5-4460', type: 'cpu', name: 'Intel Core i5-4460', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '4ª geração', cores: 4, threads: 4, image: 'CPU.png', detail: 'Intel | 4ª geração | 4 núcleos | 4 threads.' },
        { id: 'intel-core-i7-6700', type: 'cpu', name: 'Intel Core i7-6700', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '6ª geração', cores: 4, threads: 8, image: 'CPU.png', detail: 'Intel | 6ª geração | 4 núcleos | 8 threads.' },
        { id: 'intel-core-i7-7700k', type: 'cpu', name: 'Intel Core i7-7700K', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '7ª geração', cores: 4, threads: 8, image: 'CPU.png', detail: 'Intel | 7ª geração | 4 núcleos | 8 threads.' },
        { id: 'intel-core-i5-8400', type: 'cpu', name: 'Intel Core i5-8400', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '8ª geração', cores: 6, threads: 6, image: 'CPU.png', detail: 'Intel | 8ª geração | 6 núcleos | 6 threads.' },
        { id: 'intel-core-i7-9700k', type: 'cpu', name: 'Intel Core i7-9700K', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '9ª geração', cores: 8, threads: 8, image: 'CPU.png', detail: 'Intel | 9ª geração | 8 núcleos | 8 threads.' },
        { id: 'intel-core-i5-10400f', type: 'cpu', name: 'Intel Core i5-10400F', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '10ª geração', cores: 6, threads: 12, image: 'CPU.png', detail: 'Intel | 10ª geração | 6 núcleos | 12 threads.' },
        { id: 'intel-core-i7-11700k', type: 'cpu', name: 'Intel Core i7-11700K', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '11ª geração', cores: 8, threads: 16, image: 'CPU.png', detail: 'Intel | 11ª geração | 8 núcleos | 16 threads.' },
        { id: 'intel-core-i7-13700k', type: 'cpu', name: 'Intel Core i7-13700K', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '13ª geração', cores: 16, threads: 24, image: 'CPU.png', detail: 'Intel | 13ª geração | 16 núcleos | 24 threads.' },
        { id: 'intel-core-i5-14400f', type: 'cpu', name: 'Intel Core i5-14400F', manufacturer: 'Intel', category: 'Processadores', subcategory: 'CPU Intel', generation: '14ª geração', cores: 10, threads: 16, image: 'CPU.png', detail: 'Intel | 14ª geração | 10 núcleos | 16 threads.' },
        { id: 'amd-motherboard-asus-a7n8x-x', type: 'motherboard', name: 'ASUS A7N8X-X', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'Socket A', platform: 'Athlon XP', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'ASUS | Socket A | Athlon XP.' },
        { id: 'amd-motherboard-asus-a8n-e', type: 'motherboard', name: 'ASUS A8N-E', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'Socket 939', platform: 'Athlon 64', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'ASUS | Socket 939 | Athlon 64.' },
        { id: 'amd-motherboard-asus-m2n-mx', type: 'motherboard', name: 'ASUS M2N-MX', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM2', platform: 'Athlon 64 / Athlon 64 X2, conforme suporte', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'ASUS | AM2 | Athlon 64 / Athlon 64 X2.' },
        { id: 'amd-motherboard-asus-m4a78lt-m-le', type: 'motherboard', name: 'ASUS M4A78LT-M LE', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM3', platform: 'Phenom II / Athlon II, conforme suporte', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'ASUS | AM3 | Phenom II / Athlon II.' },
        { id: 'amd-motherboard-gigabyte-ga-970a-ds3p', type: 'motherboard', name: 'Gigabyte GA-970A-DS3P', manufacturer: 'Gigabyte', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM3+', platform: 'AMD FX / CPUs AM3 compatíveis', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'Gigabyte | AM3+ | AMD FX / CPUs AM3 compatíveis.' },
        { id: 'amd-motherboard-asus-a68hm-k', type: 'motherboard', name: 'ASUS A68HM-K', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'FM2+', platform: 'AMD A-Series / CPUs compatíveis', compatibility: 'Não verificada; confirme a lista oficial de CPUs suportadas.', image: 'placa-mae.png', detail: 'ASUS | FM2+ | AMD A-Series.' },
        { id: 'amd-motherboard-asus-prime-a320m-k', type: 'motherboard', name: 'ASUS PRIME A320M-K', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM4', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'ASUS | AM4 | Ryzen compatíveis.' },
        { id: 'amd-motherboard-msi-b450m-pro-vdh-max', type: 'motherboard', name: 'MSI B450M PRO-VDH MAX', manufacturer: 'MSI', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM4', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'MSI | AM4 | Ryzen compatíveis.' },
        { id: 'amd-motherboard-gigabyte-b550m-ds3h', type: 'motherboard', name: 'Gigabyte B550M DS3H', manufacturer: 'Gigabyte', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM4', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'Gigabyte | AM4 | Ryzen compatíveis.' },
        { id: 'amd-motherboard-asus-tuf-gaming-x570-plus', type: 'motherboard', name: 'ASUS TUF GAMING X570-PLUS', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM4', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'ASUS | AM4 | Ryzen compatíveis.' },
        { id: 'amd-motherboard-asus-prime-a620m-k', type: 'motherboard', name: 'ASUS PRIME A620M-K', manufacturer: 'ASUS', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM5', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'ASUS | AM5 | Ryzen compatíveis.' },
        { id: 'amd-motherboard-msi-mag-x870-tomahawk-wifi', type: 'motherboard', name: 'MSI MAG X870 TOMAHAWK WIFI', manufacturer: 'MSI', category: 'Placas-mãe', subcategory: 'Placa-Mãe AMD', socket: 'AM5', platform: 'Ryzen compatíveis', compatibility: 'Não verificada; confirme a lista oficial e a versão da BIOS.', image: 'placa-mae.png', detail: 'MSI | AM5 | Ryzen compatíveis.' },
        { id: 'techlab-b550-pro', type: 'motherboard', name: 'TechLab B550-Pro', manufacturer: 'TechLab', category: 'Placas-mãe', subcategory: 'Outras', socket: 'AM4', compatibleSockets: ['AM4'], memoryType: 'DDR4', sataPorts: 4, m2Slots: 1, image: 'placa-mae.png', detail: 'Placa-mãe representada pela bancada 2D. AM4 | DDR4 | 4x SATA | 1x M.2.' },
        { id: 'ram-2gb-ddr-400-01', type: 'ram', name: '2 GB DDR 400 MHz', capacityGb: 2, memoryStandard: 'DDR', frequencyMhz: 400, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-2gb-ddr2-800', type: 'ram', name: '2 GB DDR2 800 MHz', capacityGb: 2, memoryStandard: 'DDR2', frequencyMhz: 800, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-4gb-ddr2-800', type: 'ram', name: '4 GB DDR2 800 MHz', capacityGb: 4, memoryStandard: 'DDR2', frequencyMhz: 800, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-2gb-ddr3-1333', type: 'ram', name: '2 GB DDR3 1333 MHz', capacityGb: 2, memoryStandard: 'DDR3', frequencyMhz: 1333, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-4gb-ddr3-1600', type: 'ram', name: '4 GB DDR3 1600 MHz', capacityGb: 4, memoryStandard: 'DDR3', frequencyMhz: 1600, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-8gb-ddr3-1600', type: 'ram', name: '8 GB DDR3 1600 MHz', capacityGb: 8, memoryStandard: 'DDR3', frequencyMhz: 1600, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-4gb-ddr4-2666', type: 'ram', name: '4 GB DDR4 2666 MHz', capacityGb: 4, memoryStandard: 'DDR4', frequencyMhz: 2666, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-8gb-ddr4-3200', type: 'ram', name: '8 GB DDR4 3200 MHz', capacityGb: 8, memoryStandard: 'DDR4', memoryType: 'DDR4', frequencyMhz: 3200, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-16gb-ddr4-3200', type: 'ram', name: '16 GB DDR4 3200 MHz', capacityGb: 16, memoryStandard: 'DDR4', frequencyMhz: 3200, category: 'Memorias RAM', image: 'Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-16gb-ddr5-5600', type: 'ram', name: '16 GB DDR5 5600 MHz', capacityGb: 16, memoryStandard: 'DDR5', frequencyMhz: 5600, category: 'Memorias RAM', image: 'Mine-Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-32gb-ddr5-6000-01', type: 'ram', name: '32 GB DDR5 6000 MHz', capacityGb: 32, memoryStandard: 'DDR5', frequencyMhz: 6000, category: 'Memorias RAM', image: 'Mine-Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ram-32gb-ddr5-6000-02', type: 'ram', name: '32 GB DDR5 6000 MHz', capacityGb: 32, memoryStandard: 'DDR5', frequencyMhz: 6000, category: 'Memorias RAM', image: 'Mine-Memoria-RAM.png', compatibility: 'Não verificada; confirme o padrão e a frequência suportados pela placa-mãe.' },
        { id: 'ssd-120gb-25-sata', type: 'storage', name: '120 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '120 GB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-240gb-25-sata', type: 'storage', name: '240 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '240 GB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-250gb-25-sata', type: 'storage', name: '250 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '250 GB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-480gb-25-sata', type: 'storage', name: '480 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '480 GB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-500gb-25-sata', type: 'storage', name: '500 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '500 GB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-1tb-25-sata', type: 'storage', name: '1 TB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'SSDs', capacity: '1 TB', formFactor: '2,5"', interface: 'SATA', image: 'SSD.png', compatibility: 'Não verificada; confirme conexões SATA disponíveis.' },
        { id: 'ssd-250gb-m2-nvme-pcie', type: 'storage', name: '250 GB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '250 GB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'ssd-500gb-m2-nvme-pcie', type: 'storage', name: '500 GB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '500 GB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'ssd-1tb-m2-nvme-pcie', type: 'storage', name: '1 TB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '1 TB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'ssd-2tb-m2-nvme-pcie-01', type: 'storage', name: '2 TB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '2 TB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'ssd-2tb-m2-nvme-pcie-02', type: 'storage', name: '2 TB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '2 TB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'ssd-4tb-m2-nvme-pcie', type: 'storage', name: '4 TB | M.2 | NVMe PCIe', category: 'Armazenamento', subcategory: 'SSDs', capacity: '4 TB', formFactor: 'M.2', interface: 'NVMe PCIe', image: 'SSD.png', compatibility: 'Não verificada; confirme slot M.2 e suporte a NVMe PCIe disponíveis.' },
        { id: 'hd-250gb-35-sata-01', type: 'storage', name: '250 GB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '250 GB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-500gb-35-sata-01', type: 'storage', name: '500 GB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '500 GB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-1tb-35-sata-01', type: 'storage', name: '1 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '1 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-2tb-35-sata-01', type: 'storage', name: '2 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '2 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-4tb-35-sata-01', type: 'storage', name: '4 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '4 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-500gb-35-sata-02', type: 'storage', name: '500 GB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '500 GB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-1tb-35-sata-02', type: 'storage', name: '1 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '1 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-2tb-35-sata-02', type: 'storage', name: '2 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '2 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-1tb-35-sata-03', type: 'storage', name: '1 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '1 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-2tb-35-sata-03', type: 'storage', name: '2 TB | 3,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '2 TB', formFactor: '3,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-500gb-25-sata-01', type: 'storage', name: '500 GB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '500 GB', formFactor: '2,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'hd-1tb-25-sata-01', type: 'storage', name: '1 TB | 2,5" | SATA', category: 'Armazenamento', subcategory: 'HDs', capacity: '1 TB', formFactor: '2,5"', interface: 'SATA', image: 'HD.png', compatibility: 'Não verificada; confirme conexões SATA e espaço físico disponíveis.' },
        { id: 'gpu-nvidia-geforce-9500-gt', type: 'gpu', name: 'NVIDIA GeForce 9500 GT', manufacturer: 'NVIDIA', vramGb: 0.5, vramType: 'GDDR3', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-gtx-750-ti', type: 'gpu', name: 'NVIDIA GeForce GTX 750 Ti', manufacturer: 'NVIDIA', vramGb: 2, vramType: 'GDDR5', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-amd-radeon-r7-250', type: 'gpu', name: 'AMD Radeon R7 250', manufacturer: 'AMD', vramGb: 2, vramType: 'GDDR5', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-gtx-1050-ti', type: 'gpu', name: 'NVIDIA GeForce GTX 1050 Ti', manufacturer: 'NVIDIA', vramGb: 4, vramType: 'GDDR5', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-amd-radeon-rx-570', type: 'gpu', name: 'AMD Radeon RX 570', manufacturer: 'AMD', vramGb: 4, vramType: 'GDDR5', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-gtx-1650', type: 'gpu', name: 'NVIDIA GeForce GTX 1650', manufacturer: 'NVIDIA', vramGb: 4, vramType: 'GDDR5', powerW: 75, category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-gtx-1660-super', type: 'gpu', name: 'NVIDIA GeForce GTX 1660 SUPER', manufacturer: 'NVIDIA', vramGb: 6, vramType: 'GDDR6', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-amd-radeon-rx-6600', type: 'gpu', name: 'AMD Radeon RX 6600', manufacturer: 'AMD', vramGb: 8, vramType: 'GDDR6', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-rtx-3060', type: 'gpu', name: 'NVIDIA GeForce RTX 3060', manufacturer: 'NVIDIA', vramGb: 12, vramType: 'GDDR6', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-amd-radeon-rx-7600', type: 'gpu', name: 'AMD Radeon RX 7600', manufacturer: 'AMD', vramGb: 8, vramType: 'GDDR6', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-rtx-4060', type: 'gpu', name: 'NVIDIA GeForce RTX 4060', manufacturer: 'NVIDIA', vramGb: 8, vramType: 'GDDR6', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'gpu-nvidia-geforce-rtx-5070', type: 'gpu', name: 'NVIDIA GeForce RTX 5070', manufacturer: 'NVIDIA', vramGb: 12, vramType: 'GDDR7', category: 'Placas de video', image: 'GPU.png', compatibility: 'Não verificada; confirme o slot PCI Express, espaço e alimentação disponíveis.' },
        { id: 'psu-250w', type: 'psu', name: '250 W | Certificação não informada | ATX 20+4 pinos', category: 'Fontes', powerW: 250, certification: 'Certificação não informada', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-300w', type: 'psu', name: '300 W | Certificação não informada | ATX 20+4 pinos', category: 'Fontes', powerW: 300, certification: 'Certificação não informada', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-400w', type: 'psu', name: '400 W | 80 PLUS White | ATX 20+4 pinos', category: 'Fontes', powerW: 400, certification: '80 PLUS White', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-450w', type: 'psu', name: '450 W | 80 PLUS Bronze | ATX 20+4 pinos', category: 'Fontes', powerW: 450, certification: '80 PLUS Bronze', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-500w', type: 'psu', name: '500 W | 80 PLUS Bronze | ATX 20+4 pinos', category: 'Fontes', powerW: 500, certification: '80 PLUS Bronze', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-550w', type: 'psu', name: '550 W | 80 PLUS Bronze | ATX 20+4 pinos', category: 'Fontes', powerW: 550, certification: '80 PLUS Bronze', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-600w', type: 'psu', name: '600 W | 80 PLUS Bronze | ATX 20+4 pinos', category: 'Fontes', powerW: 600, certification: '80 PLUS Bronze', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-650w', type: 'psu', name: '650 W | 80 PLUS Bronze | ATX 20+4 pinos', category: 'Fontes', powerW: 650, certification: '80 PLUS Bronze', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-750w', type: 'psu', name: '750 W | 80 PLUS Gold | ATX 20+4 pinos', category: 'Fontes', powerW: 750, certification: '80 PLUS Gold', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-850w', type: 'psu', name: '850 W | 80 PLUS Gold | ATX 20+4 pinos', category: 'Fontes', powerW: 850, certification: '80 PLUS Gold', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-1000w', type: 'psu', name: '1000 W | 80 PLUS Gold | ATX 20+4 pinos', category: 'Fontes', powerW: 1000, certification: '80 PLUS Gold', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'psu-1200w', type: 'psu', name: '1200 W | 80 PLUS Platinum | ATX 20+4 pinos', category: 'Fontes', powerW: 1200, certification: '80 PLUS Platinum', mainConnector: 'ATX 20+4 pinos', image: 'Fonte.png', compatibility: 'Não verificada; confirme potência, conectores e requisitos dos componentes instalados.' },
        { id: 'cooler-120mm-generico', type: 'cooler', slotType: 'cooler', name: 'Cooler 120mm', category: 'Coolers', subcategory: 'Existentes', image: 'Cooler.png', detail: 'Sistema de resfriamento demonstrativo.' },
        { id: 'cable-sata-generico', type: 'cable', slotType: 'cable', name: 'Cabo SATA', category: 'Cabos', subcategory: 'Existentes', image: 'Fonte-Conectores.png', detail: 'Cabo representado por uma peca generica local.' },
        { id: 'cooler-cpu-intel-lga-775', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — Intel LGA 775', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'Intel LGA 775', compatibleSockets: ['LGA 775'], compatibility: 'Socket compativel: LGA 775. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-intel-lga-1151', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — Intel LGA 1151', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'Intel LGA 1151', compatibleSockets: ['LGA 1151'], compatibility: 'Socket compativel: LGA 1151. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-intel-lga-1200', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — Intel LGA 1200', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'Intel LGA 1200', compatibleSockets: ['LGA 1200'], compatibility: 'Socket compativel: LGA 1200. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-intel-lga-1700', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — Intel LGA 1700', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'Intel LGA 1700', compatibleSockets: ['LGA 1700'], compatibility: 'Socket compativel: LGA 1700. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-amd-am3-plus', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — AMD AM3+', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'AMD AM3+', compatibleSockets: ['AM3+'], compatibility: 'Socket compativel: AM3+. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-amd-am4', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — AMD AM4', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'AMD AM4', compatibleSockets: ['AM4'], tdpWatts: 95, compatibility: 'Socket compativel: AM4. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cooler-cpu-amd-am5', type: 'cooler-cpu', slotType: 'cooler-cpu', name: 'Cooler de CPU — AMD AM5', category: 'Coolers', subcategory: 'Coolers de CPU', specification: 'AMD AM5', compatibleSockets: ['AM5'], compatibility: 'Socket compativel: AM5. Sistema de fixacao e dimensoes: desconhecidos.', image: 'Cooler.png' },
        { id: 'cable-atx-24-pin', type: 'cable', slotType: 'cable', name: 'ATX 24 pinos', category: 'Cabos', subcategory: 'Alimentacao', connector: 'ATX 24 pinos', purpose: 'Alimentacao principal da placa-mae', compatibility: 'Conector de destino: ATX 24 pinos. Compatibilidade com a fonte e a placa-mae: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-eps-4-pin', type: 'cable', slotType: 'cable', name: 'EPS 4 pinos', category: 'Cabos', subcategory: 'Alimentacao', connector: 'EPS 4 pinos', purpose: 'Alimentacao da CPU', compatibility: 'Conector de destino: EPS 4 pinos. Compatibilidade com a fonte e a placa-mae: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-eps-8-pin-4-plus-4', type: 'cable', slotType: 'cable', name: 'EPS 8 pinos (4+4)', category: 'Cabos', subcategory: 'Alimentacao', connector: 'EPS 8 pinos (4+4)', purpose: 'Alimentacao da CPU', compatibility: 'Conector de destino: EPS 8 pinos (4+4). Compatibilidade com a fonte e a placa-mae: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-pcie-6-pin', type: 'cable', slotType: 'cable', name: 'PCIe 6 pinos', category: 'Cabos', subcategory: 'Alimentacao', connector: 'PCIe 6 pinos', purpose: 'Alimentacao de placas de video compativeis', compatibility: 'Conector de destino: PCIe 6 pinos. Compatibilidade com a fonte e a placa de video: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-pcie-8-pin-6-plus-2', type: 'cable', slotType: 'cable', name: 'PCIe 8 pinos (6+2)', category: 'Cabos', subcategory: 'Alimentacao', connector: 'PCIe 8 pinos (6+2)', purpose: 'Alimentacao de placas de video compativeis', compatibility: 'Conector de destino: PCIe 8 pinos (6+2). Compatibilidade com a fonte e a placa de video: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-12v-2x6', type: 'cable', slotType: 'cable', name: '12V-2x6', category: 'Cabos', subcategory: 'Alimentacao', connector: '12V-2x6', purpose: 'Alimentacao de placas de video compativeis', compatibility: 'Conector de destino: 12V-2x6. Compatibilidade com a fonte e a placa de video: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-sata-power', type: 'cable', slotType: 'cable', name: 'SATA Power', category: 'Cabos', subcategory: 'Alimentacao', connector: 'SATA Power', purpose: 'Alimentacao de unidades SATA', compatibility: 'Conector de destino: SATA Power. Compatibilidade com a fonte e a unidade SATA: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-molex-4-pin', type: 'cable', slotType: 'cable', name: 'Molex 4 pinos', category: 'Cabos', subcategory: 'Alimentacao', connector: 'Molex 4 pinos', purpose: 'Alimentacao de dispositivos compativeis', compatibility: 'Conector de destino: Molex 4 pinos. Compatibilidade com a fonte e o dispositivo: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-sata-data', type: 'cable', slotType: 'cable', name: 'SATA de dados', category: 'Cabos', subcategory: 'Dados', connector: 'SATA de dados', purpose: 'Conexao de dados de unidades SATA', compatibility: 'Conector de destino: SATA de dados. Compatibilidade com a placa-mae e a unidade SATA: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-usb-internal', type: 'cable', slotType: 'cable', name: 'USB interno', category: 'Cabos', subcategory: 'Dados', connector: 'USB interno', purpose: 'Conexao das portas USB frontais do gabinete', compatibility: 'Conector de destino: USB interno. Compatibilidade com a placa-mae e o gabinete: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-hd-audio-front', type: 'cable', slotType: 'cable', name: 'HD Audio frontal', category: 'Cabos', subcategory: 'Dados', connector: 'HD Audio frontal', purpose: 'Conexao de audio do gabinete', compatibility: 'Conector de destino: HD Audio frontal. Compatibilidade com a placa-mae e o gabinete: desconhecida.', image: 'Fonte-Conectores.png' },
        { id: 'cable-fan-4-pin-pwm', type: 'cable', slotType: 'cable', name: 'Ventoinha 4 pinos PWM', category: 'Cabos', subcategory: 'Ventoinhas', connector: 'Ventoinha 4 pinos PWM', purpose: 'Conexao de ventoinhas compativeis', compatibility: 'Conector de destino: Ventoinha 4 pinos PWM. Compatibilidade com a placa-mae e a ventoinha: desconhecida.', image: 'Fonte-Conectores.png' }
    ];
    const slotDefinitions = {
        cpu: { position: [-150, 105], size: [92, 92], label: 'SOCKET CPU' },
        ram: { position: [45, 105], size: [34, 155], label: 'SLOTS RAM' },
        gpu: { position: [-80, -105], size: [270, 30], label: 'PCI EXPRESS' },
        storage: { position: [-145, -45], size: [105, 25], label: 'M.2 NVMe' },
        cooler: { position: [-150, 105], size: [112, 112], label: 'COOLER' },
        'cooler-cpu': { position: [-150, 105], size: [112, 112], label: 'COOLER CPU' }
    };
    const cableSlots = [
        ['ATX 24 pinos', 90, 190], ['EPS 4 pinos', 90, 160], ['EPS 8 pinos (4+4)', 90, 130],
        ['PCIe 6 pinos', 90, 100], ['PCIe 8 pinos (6+2)', 90, 70], ['12V-2x6', 90, 40],
        ['SATA Power', 90, 10], ['Molex 4 pinos', 90, -20], ['SATA de dados', 90, -50],
        ['USB interno', 90, -80], ['HD Audio frontal', 90, -110], ['Ventoinha 4 pinos PWM', 90, -140]
    ].map(([connector, x, y]) => ({ connector, position: [x, y], size: [52, 16], label: connector }));
    const state = { zoom: 1, selected: null, dragged: null, installed: [], history: [], moving: null, preview: null };
    let scene;
    let camera;
    let renderer;
    let board;
    let raycaster;
    let pointer;

    function initThree() {
        if (!window.THREE) {
            if (result) {
                result.textContent = 'Three.js não foi carregado. A bancada não pode ser iniciada.';
                result.className = 'diagnostic-result warning';
            }
            return;
        }
        scene = new THREE.Scene();
        camera = new THREE.OrthographicCamera(-500, 500, 300, -300, 0.1, 1000);
        camera.position.z = 10;
        renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        raycaster = new THREE.Raycaster();
        pointer = new THREE.Vector2();
        createBoard();
        resize();
        window.addEventListener('resize', resize);
        canvas.addEventListener('pointerdown', pointerDown);
        canvas.addEventListener('pointermove', pointerMove);
        canvas.addEventListener('pointerup', pointerUp);
        requestAnimationFrame(render);
    }

    function makeRect(width, height, color, x, y, z = 0) {
        const mesh = new THREE.Mesh(new THREE.PlaneGeometry(width, height), new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 1 }));
        mesh.position.set(x, y, z);
        return mesh;
    }

    function createBoard() {
        const grid = new THREE.GridHelper(1000, 40, 0xdbeafe, 0xf1f5f9);
        grid.rotation.x = Math.PI / 2;
        grid.position.z = -2;
        scene.add(grid);
        board = makeRect(420, 470, 0x172b3a, -90, 5, 0);
        scene.add(board);
        scene.add(makeRect(385, 435, 0x203f43, -90, 5, 1));
        const labels = makeRect(260, 24, 0x0f2f24, -90, 210, 2);
        scene.add(labels);
        Object.entries(slotDefinitions).forEach(([type, slot]) => {
            const [width, height] = slot.size;
            const zone = makeRect(width, height, type === 'ram' ? 0x2563eb : 0x60a5fa, slot.position[0], slot.position[1], 2);
            zone.material.opacity = 0.16;
            zone.userData.slotType = type;
            scene.add(zone);
            const outline = makeRect(width + 5, height + 5, 0x93c5fd, slot.position[0], slot.position[1], 1.8);
            outline.material.opacity = 0.18;
            scene.add(outline);
        });
        cableSlots.forEach((slot) => {
            const zone = makeRect(slot.size[0], slot.size[1], 0xf59e0b, slot.position[0], slot.position[1], 2);
            zone.material.opacity = 0.16;
            scene.add(zone);
        });
        scene.add(makeRect(150, 125, 0x1e293b, 305, -130, 0));
        scene.add(makeRect(110, 90, 0x0f172a, 305, -130, 1));
    }

    function resize() {
        if (!renderer) return;
        const rect = stage.getBoundingClientRect();
        renderer.setSize(rect.width, rect.height, false);
        const aspect = rect.width / Math.max(rect.height, 1);
        const viewHeight = 620 / state.zoom;
        camera.top = viewHeight / 2;
        camera.bottom = -viewHeight / 2;
        camera.left = -(viewHeight * aspect) / 2;
        camera.right = (viewHeight * aspect) / 2;
        camera.updateProjectionMatrix();
    }

    function render() {
        if (renderer) renderer.render(scene, camera);
        requestAnimationFrame(render);
    }

    function worldPosition(event) {
        const rect = canvas.getBoundingClientRect();
        pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        raycaster.setFromCamera(pointer, camera);
        const point = raycaster.ray.origin.clone();
        return { x: point.x, y: point.y };
    }

    function textureFor(component) {
        const texture = new THREE.TextureLoader().load(imageBase + component.image);
        return texture;
    }

    function createComponent(component, position, snapped = false) {
        const material = new THREE.MeshBasicMaterial({ map: textureFor(component), transparent: true, color: 0xffffff });
        const size = componentSize(component);
        const mesh = new THREE.Mesh(new THREE.PlaneGeometry(size[0], size[1]), material);
        mesh.position.set(position.x, position.y, 5);
        mesh.userData = { component, snapped, size };
        scene.add(mesh);
        state.installed.push(mesh);
        selectMesh(mesh);
        return mesh;
    }

    function componentSize(component) {
        return component.type === 'ram' ? [26, 125] : component.type === 'gpu' ? [200, 45] : ['cooler', 'cooler-cpu'].includes(component.type) ? [85, 85] : component.type === 'cable' ? [48, 12] : [70, 55];
    }

    function updateDragPreview(position) {
        if (!state.dragged || !scene) return;
        if (!state.preview) {
            const size = componentSize(state.dragged);
            const material = new THREE.MeshBasicMaterial({ map: textureFor(state.dragged), transparent: true, opacity: 0.45, color: 0x93c5fd });
            state.preview = new THREE.Mesh(new THREE.PlaneGeometry(size[0], size[1]), material);
            state.preview.position.z = 6;
            scene.add(state.preview);
        }
        state.preview.position.set(position.x, position.y, 6);
    }

    function clearDragPreview() {
        if (!state.preview) return;
        scene.remove(state.preview);
        state.preview.material.map?.dispose();
        state.preview.material.dispose();
        state.preview.geometry.dispose();
        state.preview = null;
    }

    function nearestSlot(component, position) {
        if (component.type === 'cable' && component.connector) {
            const cableSlot = cableSlots.find((slot) => slot.connector === component.connector);
            if (!cableSlot) return null;
            const distance = Math.hypot(position.x - cableSlot.position[0], position.y - cableSlot.position[1]);
            return distance < 34 ? cableSlot : null;
        }
        const slot = slotDefinitions[component.slotType || component.type];
        if (!slot) return null;
        const distance = Math.hypot(position.x - slot.position[0], position.y - slot.position[1]);
        return distance < (component.type === 'gpu' ? 105 : 68) ? slot : null;
    }

    function compatibilityIssue(component) {
        const motherboard = state.installed.map((mesh) => mesh.userData.component).find((item) => item.type === 'motherboard');
        const cpu = state.installed.map((mesh) => mesh.userData.component).find((item) => item.type === 'cpu');
        const psu = state.installed.map((mesh) => mesh.userData.component).find((item) => item.type === 'psu');
        const gpu = state.installed.map((mesh) => mesh.userData.component).find((item) => item.type === 'gpu');

        // Cooler vs CPU socket + TDP
        if (component.type === 'cooler-cpu') {
            if (!motherboard || !motherboard.socket || !component.compatibleSockets) return null;
            if (!component.compatibleSockets.includes(motherboard.socket)) {
                return `Incompatibilidade: ${component.name} nao suporta o socket ${motherboard.socket} da placa-mãe.`;
            }
            if (cpu && component.tdpWatts && cpu.tdpWatts && component.tdpWatts < cpu.tdpWatts) {
                return `Aviso: ${component.name} pode nao resfriar adequadamente o ${cpu.name} (TDP ${cpu.tdpWatts}W).`;
            }
            return null;
        }

        // CPU vs Motherboard socket
        if (component.type === 'cpu') {
            if (!motherboard || !motherboard.socket || !component.compatibleSockets) return null;
            if (!component.compatibleSockets.includes(motherboard.socket)) {
                return `Incompatibilidade: ${component.name} usa socket ${component.socket || 'desconhecido'}, placa-mae e ${motherboard.socket}.`;
            }
            return null;
        }

        // RAM vs Motherboard memory type
        if (component.type === 'ram') {
            if (!motherboard || !motherboard.memoryType) return null;
            if (component.memoryType && component.memoryType !== motherboard.memoryType) {
                return `Incompatibilidade: ${component.name} e ${component.memoryType}, placa-mae suporta ${motherboard.memoryType}.`;
            }
            return null;
        }

        // Storage interface vs Motherboard slots
        if (component.type === 'storage') {
            if (!motherboard) return null;
            if (component.interface === 'SATA' && (!motherboard.sataPorts || motherboard.sataPorts < 1)) {
                return `Incompatibilidade: placa-mae nao possui portas SATA disponiveis.`;
            }
            if (component.interface === 'M.2' && (!motherboard.m2Slots || motherboard.m2Slots < 1)) {
                return `Incompatibilidade: placa-mae nao possui slots M.2 disponiveis.`;
            }
            return null;
        }

        // GPU power vs PSU
        if (component.type === 'gpu') {
            if (!psu) return null;
            const gpuPower = component.powerW || 0;
            const psuWatt = psu.powerW || 0;
            const cpuPower = cpu ? (cpu.tdpWatts || 0) : 65;
            const estimado = gpuPower + cpuPower + 50;
            if (estimado > psuWatt) {
                return `Aviso: Fonte de ${psuWatt}W pode ser insuficiente para ${component.name} + sistema (estimado ${estimado}W).`;
            }
            return null;
        }

        return null;
    }

    function placeComponent(component, position) {
        const compatibilityError = compatibilityIssue(component);
        if (compatibilityError) {
            showToast(compatibilityError);
            return null;
        }
        const slot = nearestSlot(component, position);
        if (slot) {
            saveHistory();
            const mesh = createComponent(component, { x: slot.position[0], y: slot.position[1] }, true);
            showToast(`${component.name} encaixado no ${slot.label}.`);
            return mesh;
        }
        showToast(`Posição inválida. Arraste ${component.name} até um encaixe compatível.`);
        return null;
    }

    function selectMesh(mesh) {
        if (state.selected && state.selected.material) state.selected.material.color.setHex(0xffffff);
        state.selected = mesh;
        if (mesh) {
            mesh.material.color.setHex(0x93c5fd);
            const { component, snapped } = mesh.userData;
            componentInfo.innerHTML = `<strong>${escapeHtml(component.name)}</strong><p>${escapeHtml(componentDetails(component))}</p><p>Status: ${snapped ? 'instalado em um encaixe válido' : 'selecionado na bancada'}</p>`;
        }
    }

    function snapshotMesh(mesh) {
        return { component: mesh.userData.component, x: mesh.position.x, y: mesh.position.y, snapped: mesh.userData.snapped };
    }

    function pointerDown(event) {
        const position = worldPosition(event);
        raycaster.intersectObjects(state.installed).some((hit) => {
            if (hit.object === state.selected || hit.object.userData) {
                selectMesh(hit.object);
                state.moving = { mesh: hit.object, before: snapshotMesh(hit.object), offset: { x: hit.object.position.x - position.x, y: hit.object.position.y - position.y } };
                canvas.setPointerCapture(event.pointerId);
                return true;
            }
            return false;
        });
    }

    function pointerMove(event) {
        if (!state.moving) return;
        const position = worldPosition(event);
        state.moving.mesh.position.x = position.x + state.moving.offset.x;
        state.moving.mesh.position.y = position.y + state.moving.offset.y;
        state.moving.mesh.userData.snapped = false;
    }

    function pointerUp(event) {
        if (!state.moving) return;
        const mesh = state.moving.mesh;
        const slot = nearestSlot(mesh.userData.component, { x: mesh.position.x, y: mesh.position.y });
        if (slot) {
            saveHistory([state.moving.before]);
            mesh.position.set(slot.position[0], slot.position[1], 5);
            mesh.userData.snapped = true;
            showToast(`${mesh.userData.component.name} encaixado novamente.`);
        } else {
            mesh.position.set(state.moving.before.x, state.moving.before.y, 5);
            mesh.userData.snapped = state.moving.before.snapped;
            showToast('Componente reposicionado fora de um encaixe. Use um slot compatível.');
        }
        state.moving = null;
        canvas.releasePointerCapture?.(event.pointerId);
    }

    function saveHistory(snapshot = state.installed.map(snapshotMesh)) {
        state.history.push(snapshot);
        if (state.history.length > 20) state.history.shift();
    }

    function undo() {
        const snapshot = state.history.pop();
        if (!snapshot) return showToast('Não há ações para desfazer.');
        state.installed.forEach((mesh) => scene.remove(mesh));
        state.installed = [];
        snapshot.forEach((item) => createComponent(item.component, { x: item.x, y: item.y }, item.snapped));
        state.selected = null;
        showToast('Última ação desfeita.');
    }

    function removeSelected() {
        if (!state.selected) return showToast('Selecione um componente para excluir.');
        saveHistory();
        scene.remove(state.selected);
        state.installed = state.installed.filter((mesh) => mesh !== state.selected);
        state.selected = null;
        componentInfo.innerHTML = '<strong>Nenhum componente selecionado</strong><p>Selecione uma peça na bancada ou na biblioteca.</p>';
        showToast('Componente removido da bancada.');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character]);
    }

    function componentDetails(component) {
        const metadata = [component.manufacturer, component.capacity, component.formFactor, component.interface, component.powerW ? `${component.powerW} W` : '', component.certification, component.mainConnector, component.capacityGb ? `${component.capacityGb} GB` : '', component.memoryStandard, component.frequencyMhz ? `${component.frequencyMhz} MHz` : '', component.vramGb !== undefined ? `${component.vramGb} GB VRAM` : '', component.vramType, component.subcategory, component.generation, component.cores ? `${component.cores} núcleos` : '', component.threads ? `${component.threads} threads` : '', component.socket, component.platform, component.specification, component.connector, component.purpose, component.compatibility].filter(Boolean);
        return metadata.length ? metadata.join(' | ') : component.detail || 'Compatibilidade não verificada.';
    }

    function renderComponentItems(items) {
        return items.map((item) => `<div class="component-item" draggable="true" data-id="${escapeHtml(item.id || `${item.type}-${item.name}`)}"><img src="${imageBase}${item.image}" alt=""><span><strong>${escapeHtml(item.name)}</strong><small>${escapeHtml(componentDetails(item))}</small></span></div>`).join('');
    }

    function renderLibrary(query = '') {
        const normalizedQuery = query.toLocaleLowerCase('pt-BR');
        const filtered = components.filter((item) => [item.name, item.category, item.subcategory, item.manufacturer, item.generation, item.socket, item.platform, item.capacity, item.formFactor, item.interface, item.powerW, item.certification, item.mainConnector, item.capacityGb, item.memoryStandard, item.frequencyMhz, item.vramGb, item.vramType, item.specification, item.connector, item.purpose, item.compatibility, item.detail].filter(Boolean).join(' ').toLocaleLowerCase('pt-BR').includes(normalizedQuery));
        const groups = [...new Set(filtered.map((item) => item.category))];
        componentList.innerHTML = groups.map((category) => {
            const categoryItems = filtered.filter((item) => item.category === category);
            if (!['Processadores', 'Armazenamento', 'Coolers', 'Cabos'].includes(category)) return `<details class="component-category" open><summary>${escapeHtml(category)}</summary><div class="component-list">${renderComponentItems(categoryItems)}</div></details>`;
            const subcategories = [...new Set(categoryItems.map((item) => item.subcategory || 'Outros'))].sort((left, right) => category === 'Armazenamento' ? ({ HDs: 0, SSDs: 1 }[left] ?? 2) - ({ HDs: 0, SSDs: 1 }[right] ?? 2) : 0);
            return `<details class="component-category" open><summary>${escapeHtml(category)}</summary><div class="component-subcategories">${subcategories.map((subcategory) => `<details class="component-category component-subcategory" open><summary>${escapeHtml(subcategory)}</summary><div class="component-list">${renderComponentItems(categoryItems.filter((item) => (item.subcategory || 'Outros') === subcategory))}</div></details>`).join('')}</div></details>`;
        }).join('');
        componentList.querySelectorAll('.component-item').forEach((item) => {
            const component = components.find((entry) => (entry.id || `${entry.type}-${entry.name}`) === item.dataset.id);
            item.addEventListener('dragstart', (event) => {
                state.dragged = component;
                event.dataTransfer.setData('text/plain', component.name);
                event.dataTransfer.effectAllowed = 'copy';
            });
            item.addEventListener('dragend', clearDragPreview);
            item.addEventListener('click', () => {
                componentInfo.innerHTML = `<strong>${escapeHtml(component.name)}</strong><p>${escapeHtml(componentDetails(component))}</p><p>Arraste esta peça para a bancada para instalar.</p>`;
                document.querySelector('[data-tab="info"]').click();
            });
        });
    }

    function dropFromLibrary(event) {
        event.preventDefault();
        stage.classList.remove('is-drop-target');
        if (!state.dragged) return;
        const position = worldPosition(event);
        placeComponent(state.dragged, position);
        clearDragPreview();
        state.dragged = null;
    }

    function testSystem() {
        const installed = state.installed.map((m) => m.userData.component);
        const snapped = state.installed.filter((m) => m.userData.snapped).map((m) => m.userData.component);

        const hasMotherboard = snapped.some((c) => c.type === 'motherboard');
        const hasCpu = snapped.some((c) => c.type === 'cpu');
        const hasRam = snapped.some((c) => c.type === 'ram');
        const hasStorage = snapped.some((c) => c.type === 'storage');
        const hasGpu = snapped.some((c) => c.type === 'gpu');
        const hasCpuCooler = snapped.some((c) => c.type === 'cooler-cpu');
        const hasPsu = snapped.some((c) => c.type === 'psu');

        const cpu = snapped.find((c) => c.type === 'cpu');
        const motherboard = snapped.find((c) => c.type === 'motherboard');
        const psu = snapped.find((c) => c.type === 'psu');
        const gpu = snapped.find((c) => c.type === 'gpu');

        const issues = [];
        const warnings = [];
        const infos = [];

        if (!hasMotherboard) issues.push('Placa-mãe não encontrada no slot.');
        if (!hasCpu) issues.push('Processador não encontrado no soquete.');
        if (!hasRam) issues.push('Memória RAM não encontrada no slot.');
        if (!hasStorage) warnings.push('Nenhum dispositivo de armazenamento detectado.');
        if (!hasCpuCooler) warnings.push('Cooler de processador não detectado (risco de superaquecimento).');
        if (!hasPsu) issues.push('Fonte de alimentação não conectada.');

        if (hasCpu && hasMotherboard && cpu && motherboard.socket && cpu.compatibleSockets && !cpu.compatibleSockets.includes(motherboard.socket)) {
            issues.push(`Processador ${cpu.name} incompatível com o soquete ${motherboard.socket} da placa-mãe.`);
        }
        if (hasRam && hasMotherboard) {
            const ram = snapped.find((c) => c.type === 'ram');
            if (motherboard.memoryType && ram.memoryType && ram.memoryType !== motherboard.memoryType) {
                issues.push(`Memória ${ram.memoryType} incompatível com placa-mãe (${motherboard.memoryType}).`);
            }
        }
        if (hasGpu && hasPsu) {
            const gpuPower = gpu.powerW || 0;
            const psuWatt = psu.powerW || 0;
            const cpuPower = cpu ? (cpu.tdpWatts || 0) : 65;
            const estimado = gpuPower + cpuPower + 50;
            if (estimado > psuWatt) {
                warnings.push(`Fonte pode ser insuficiente (estimado ${estimado}W, fonte ${psuWatt}W).`);
            }
        }

        if (!hasGpu && hasCpu) {
            const cpuHasVideo = (cpu.vramGb !== undefined && cpu.vramGb > 0) || (cpu.subcategory && cpu.subcategory.includes('Integrada'));
            if (!cpuHasVideo) {
                infos.push('Processador sem vídeo integrado: placa de vídeo dedicada recomendada para exibição.');
            }
        }

        result.className = 'diagnostic-result';
        let mensagem = '';
        let classe = 'success';

        if (issues.length > 0) {
            classe = 'error';
            mensagem = 'Falha de montagem:\n• ' + issues.join('\n• ');
        } else if (warnings.length > 0) {
            classe = 'warning';
            mensagem = 'Montagem com ressalvas:\n• ' + warnings.join('\n• ');
        } else {
            mensagem = 'Verificação concluída: todos os componentes essenciais estão instalados e compatíveis.';
        }

        if (infos.length > 0) {
            mensagem += '\n\nRecomendações:\n• ' + infos.join('\n• ');
        }

        result.textContent = mensagem;
        result.classList.add(classe);
        result.style.whiteSpace = 'pre-line';

        // Salvar diagnostico no backend se houver usuario logado
        const usuario = window.__labUsuario || null;
        if (usuario) {
            const totalPeso = snapped.reduce((acc, c) => acc + (c.pesoKg || 0), 0);
            const totalPotencia = snapped.reduce((acc, c) => acc + (c.powerW || 0), 0);
            saveDiagnostico({
                tipo: 'montagem',
                status: classe === 'success' ? 'SUCESSO' : classe === 'warning' ? 'ALERTA' : 'FALHA',
                descricao: mensagem,
                resultado: JSON.stringify({ issues, warnings, infos, componentCount: snapped.length }),
                temperaturaSimulada: 45 + Math.random() * 15,
                potenciaTotalSimulada: totalPotencia,
            });
        }
    }

    function zoom(delta) { state.zoom = Math.min(1.45, Math.max(.75, state.zoom + delta)); zoomLevel.value = `${Math.round(state.zoom * 100)}%`; resize(); }
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function saveBuild(payload) {
        const token = getCsrfToken();
        const url = '../api/salvar-build.php' + (token ? '?csrf_token=' + encodeURIComponent(token) : '');
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json; charset=utf-8' },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (data.ok) {
                showToast(data.mensagem || 'Build salva com sucesso.');
                return data;
            }
            showToast(data.erro || 'Erro ao salvar build.');
            return data;
        } catch (error) {
            showToast('Erro de rede ao salvar build.');
            return { ok: false, erro: error.message };
        }
    }

    async function saveDiagnostico(payload) {
        const token = getCsrfToken();
        const url = '../api/salvar-diagnostico.php' + (token ? '?csrf_token=' + encodeURIComponent(token) : '');
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json; charset=utf-8' },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (data.ok) {
                showToast(data.mensagem || 'Diagnostico salvo com sucesso.');
                return data;
            }
            showToast(data.erro || 'Erro ao salvar diagnostico.');
            return data;
        } catch (error) {
            showToast('Erro de rede ao salvar diagnostico.');
            return { ok: false, erro: error.message };
        }
    }

    function showToast(message) { let toast = document.querySelector('.toast'); if (!toast) { toast = document.createElement('div'); toast.className = 'toast'; document.body.appendChild(toast); } toast.textContent = message; clearTimeout(window.toastTimeout); window.toastTimeout = setTimeout(() => toast.remove(), 2800); }

    document.getElementById('undo-button').addEventListener('click', undo);
    document.getElementById('delete-button').addEventListener('click', removeSelected);
    document.getElementById('zoom-in').addEventListener('click', () => zoom(.1));
    document.getElementById('zoom-out').addEventListener('click', () => zoom(-.1));
    const testButton = document.getElementById('test-system');
    if (testButton) testButton.addEventListener('click', testSystem);
    componentSearch.addEventListener('input', () => renderLibrary(componentSearch.value));
    stage.addEventListener('dragover', (event) => { event.preventDefault(); stage.classList.add('is-drop-target'); updateDragPreview(worldPosition(event)); });
    stage.addEventListener('dragleave', () => { stage.classList.remove('is-drop-target'); clearDragPreview(); });
    stage.addEventListener('drop', dropFromLibrary);
    document.querySelectorAll('.lab-tabs button').forEach((button) => button.addEventListener('click', () => { document.querySelectorAll('.lab-tabs button').forEach((item) => item.classList.toggle('active', item === button)); document.getElementById('diagnostic-panel').hidden = button.dataset.tab !== 'diagnostic'; document.getElementById('info-panel').hidden = button.dataset.tab !== 'info'; }));
    const helpButton = document.getElementById('help-button');
    const closeHelpButton = document.getElementById('close-help');
    if (helpButton && helpModal) helpButton.addEventListener('click', () => { helpModal.hidden = false; });
    if (closeHelpButton && helpModal) closeHelpButton.addEventListener('click', () => { helpModal.hidden = true; });
    if (helpModal) helpModal.addEventListener('click', (event) => { if (event.target === helpModal) helpModal.hidden = true; });
        const usuarioMeta = document.querySelector('meta[name="lab-usuario"]');
    window.__labUsuario = usuarioMeta ? JSON.parse(usuarioMeta.getAttribute('content') || '{}') : null;

    let labMode = '2d';
    const modeButton = document.querySelector('.lab-mode');
    if (modeButton) {
        modeButton.addEventListener('click', () => {
            labMode = labMode === '2d' ? 'slots' : '2d';
            modeButton.textContent = labMode === '2d' ? '2D' : 'SLOTS';
            stage.classList.toggle('mode-slots', labMode === 'slots');
            showToast('Modo: ' + (labMode === '2d' ? 'Bancada 2D' : 'Selecao de Slots'));
        });
    }

    const saveButton = document.getElementById('save-build');
    if (saveButton) {
        saveButton.addEventListener('click', async () => {
            const snapped = state.installed.filter((m) => m.userData.snapped);
            const componentes = snapped.map((mesh, index) => {
                const comp = mesh.userData.component;
                return {
                    id_componente: comp.id_componente || null,
                    slot_instalacao: mesh.userData.snapped ? 'slot-' + index : null,
                    ordem_instalacao: index,
                };
            });
            const nome = prompt('Nome da build:', 'Minha Montagem ' + new Date().toLocaleDateString('pt-BR'));
            if (!nome) return;
            const payload = {
                nome,
                descricao: 'Build criada no Laboratorio Virtual',
                componentes,
                status: 'COMPLETA',
            };
            await saveBuild(payload);
        });
    }

    renderLibrary();
    initThree();
});
