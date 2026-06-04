# BAB I TEST FORMATTING

## 1.1 Paragraf Biasa

Sistem manajemen inventori merupakan komponen penting dalam operasional sebuah kafe. Tanpa sistem inventory yang baik, kafe dapat mengalami kehabisan stok bahan baku yang mengakibatkan menu tidak tersedia, atau justru kelebihan stok yang berujung pada pemborosan dan kadaluarsa bahan. Penelitian sebelumnya menunjukkan bahwa implementasi sistem inventory berbasis web dapat mengurangi kesalahan pencatatan stok hingga 85% dibandingkan metode manual [1].

Sistem inventory modern umumnya menggunakan metode FIFO (*First-In-First-Out*) atau FEFO (*First-Expiry-First-Out*) untuk mengelola siklus hidup bahan baku. Algoritma FIFO memastikan bahwa bahan yang diterima lebih awal akan digunakan terlebih dahulu, sementara algoritma FEFO memprioritaskan bahan yang memiliki tanggal kadaluarsa paling dekat. Pemilihan metode yang tepat sangat bergantung pada karakteristik bahan baku yang dikelola.

## 1.2 Kutipan Source Code

Berikut adalah contoh implementasi algoritma FEFO dalam bahasa PHP menggunakan framework Laravel:

```php
public function deductIngredientStock(
    int $ingredientId,
    float $requiredQuantity,
    array $context = [],
    bool $skipStockValidation = false
): array {
    $ingredient = Ingredient::findOrFail($ingredientId);
    $batches = $ingredient->batches()
        ->where('quantity', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->lockForUpdate()
        ->get();

    $deducted = 0;
    foreach ($batches as $batch) {
        if ($deducted >= $requiredQuantity) break;
        $taken = min($batch->quantity, $requiredQuantity - $deducted);
        $batch->decrement('quantity', $taken);
        $deducted += $taken;
    }

    return ['success' => true, 'total_deducted' => $deducted];
}
```

Sedangkan untuk implementasi FIFO, perbedaan hanya terletak pada urutan batch yang digunakan:

```python
def fifo_deduction(batches, required_qty):
    """
    Melakukan deduksi stok menggunakan metode FIFO.
    Batch dengan received_at terlama akan digunakan terlebih dahulu.
    """
    sorted_batches = sorted(
        batches,
        key=lambda b: b.received_at or b.created_at
    )

    deducted = 0.0
    movements = []

    for batch in sorted_batches:
        if deducted >= required_qty:
            break

        take = min(batch.quantity, required_qty - deducted)

        movements.append({
            'batch_id': batch.id,
            'quantity_before': batch.quantity,
            'quantity_change': -take,
            'quantity_after': batch.quantity - take
        })

        batch.quantity -= take
        deducted += take

    return {
        'success': True,
        'total_deducted': deducted,
        'movements': movements
    }
```

## 1.3 Tabel Data

Tabel berikut menunjukkan perbandingan antara metode FIFO dan FEFO:

| Metode | Sort Order | Kelebihan | Kekurangan |
|--------|-----------|-----------|------------|
| FIFO | received_at ASC | Cocok untuk bahan non-kadaluarsa | Tidak memprioritaskan expiry date |
| FEFO | expiry_date ASC | Mengurangi waste bahan kadaluarsa | Membutuhkan data expiry date yang akurat |

## 1.4 Penjelasan dengan Gambar

![Logo Laravel](https://laravel.com/img/logomark.min.svg)

Gambar di atas menunjukkan logo Laravel, framework yang digunakan dalam pengembangan sistem inventory ini.

### 1.4.1 Integrasi dengan Modul Lain

Sistem inventory ini terintegrasi dengan modul kasir (*cashier module*) dan modul sinkronisasi offline (*offline sync module*). Integrasi ini memungkinkan deduksi stok terjadi secara real-time ketika kasir melakukan konfirmasi pembayaran.

Namun demikian, terdapat beberapa tantangan dalam implementasi integrasi ini, terutama terkait dengan *race condition* pada saat transaksi konkuren terjadi. Untuk mengatasinya, digunakan pessimistic locking (`lockForUpdate()`) pada setiap operasi deduksi batch.

## 1.5 Abstrak dan Abstract

Abstrak ditulis dengan format Times New Roman 10pt, spacing 1.0. Sedangkan Abstract menggunakan Times New Roman 10pt, spacing 1.0, dengan seluruh teks menggunakan huruf miring (*italic*).

# BAB II TEST REFERENSI

## 2.1 Sumber Referensi

Pada bagian ini akan didemonstrasikan penggunaan referensi dengan format IEEE. Beberapa penelitian sebelumnya telah membahas implementasi sistem inventory menggunakan berbagai metode.

Metode FIFO telah banyak diimplementasikan dalam sistem informasi inventory dan terbukti efektif untuk mengelola bahan baku dengan masa simpan yang panjang [1]. Sementara itu, metode FEFO lebih direkomendasikan untuk bahan baku yang memiliki tanggal kadaluarsa karena dapat meminimalkan waste [1], [2].

Penelitian lain menunjukkan bahwa implementasi sistem inventory berbasis web menggunakan Laravel dapat meningkatkan efisiensi pencatatan stok hingga 90% [3]. Framework Laravel dipilih karena menyediakan struktur aplikasi yang terorganisir, mendukung pengembangan web modern, dan memiliki ekosistem yang luas termasuk Filament untuk admin panel [4].

Selain itu, terdapat penelitian yang membahas tentang pentingnya pemilihan algoritma konsumsi batch yang tepat dalam sistem inventory untuk menghindari kerugian akibat bahan kadaluarsa [5]. Algoritma FEFO terutama sangat cocok untuk industri makanan dan minuman karena memprioritaskan bahan yang akan kadaluarsa terlebih dahulu.

## 2.2 Tabel Referensi

Berikut adalah daftar referensi yang digunakan dalam penelitian ini:

| No | Metode | Kelebihan | Contoh Implementasi |
|:--:|--------|-----------|-------------------|
| 1 | FIFO | Sederhana, mudah diimplementasikan | Sistem inventory gudang |
| 2 | FEFO | Mengurangi waste | Industri makanan & minuman |
| 3 | Hybrid | Fleksibel | Sistem POS restoran |

# BAB III TEST TAMBAHAN

## 3.1 Paragraf dengan Berbagai Gaya Teks

Teks ini adalah contoh penggunaan berbagai gaya penulisan. Kata dalam bahasa Inggris seperti *database*, *framework*, *server*, dan *middleware* harus ditulis dengan huruf miring secara otomatis. Begitu pula dengan istilah teknis seperti *application programming interface* (API) dan *representational state transfer* (REST).

Penting untuk diperhatikan bahwa penulisan kata asing harus konsisten menggunakan huruf miring (*italic*) di seluruh dokumen. Script akan mendeteksi kata-kata tersebut berdasarkan daftar istilah teknis yang telah didefinisikan.

### 3.1.1 Sub Sub Bab Testing

Ini adalah contoh heading level 3 yang digunakan untuk sub-sub bab. Heading level 3 menggunakan format `3.1.1` dengan font Times New Roman 12pt bold.

```javascript
// Contoh kode JavaScript untuk frontend
async function fetchStockData(menuId) {
    const response = await fetch(`/api/menu/${menuId}/stock`);
    const data = await response.json();

    if (data.stock < data.lowStockThreshold) {
        showLowStockWarning(menuId, data.stock);
        return false;
    }

    return data.stock;
}
```

## 3.2 Test Paragraf Panjang

Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

Paragraf ini menguji apakah *justify alignment* bekerja dengan benar. Setiap paragraf harus rata kanan-kiri dengan indentasi 1,25 cm di baris pertama. Spasi antar baris adalah 1,5 spasi. Semua teks body menggunakan font Times New Roman ukuran 12pt.

# BAB IV PENUTUP

## 4.1 Kesimpulan

Berdasarkan hasil pengujian formatting yang telah dilakukan, dapat disimpulkan bahwa:

1. Script berhasil menghasilkan berbagai format heading sesuai standar skripsi
2. Source code berhasil ditampilkan dalam format yang benar
3. Referensi IEEE dapat ditulis dengan format yang sesuai
4. Kata asing berhasil dideteksi dan di-italic secara otomatis

## 4.2 Saran

Untuk pengembangan selanjutnya, disarankan untuk menambahkan fitur daftar isi otomatis dan daftar gambar yang dapat di-update secara langsung tanpa harus membuka file di Microsoft Word.

# BAB V TEST LANJUTAN

## 5.1 Pengujian Lanjutan

Ini adalah bab kelima untuk menguji apakah penomoran berlanjut dengan benar setelah bab sebelumnya. Sub-bab ini harus menunjukkan angka "5.1" bukan "1.1".

## 5.2 Verifikasi Numbering

Sub-bab ini harus menunjukkan "5.2" untuk memastikan bahwa increment dalam satu bab bekerja dengan benar.

### 5.2.1 Sub Testing

Sub-sub-bab ini harus menunjukkan "5.2.1".

### 5.2.2 Sub Testing Lain

Sub-sub-bab ini harus menunjukkan "5.2.2".

## 5.3 Test Terakhir

Sub-bab ini harus menunjukkan "5.3".

# BAB VI PENUTUP

## 6.1 Kesimpulan Akhir

Bab terakhir ini harus menunjukkan "6.1" untuk sub-bab pertamanya.

## 6.2 Saran Akhir

Sub-bab ini harus menunjukkan "6.2".

### 6.2.1 Saran Teknis

Sub-sub-bab ini harus menunjukkan "6.2.1".

### 6.2.2 Saran Non-Teknis

Sub-sub-bab ini harus menunjukkan "6.2.2".
