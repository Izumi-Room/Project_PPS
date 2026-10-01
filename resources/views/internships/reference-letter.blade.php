<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Pengantar Magang - {{ $internship->student_nim }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 2cm 2.5cm 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
        }
        .paper {
            background: #fff;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 2.5cm 2.5cm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 24px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 1px;
        }
        .header h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 9.5pt;
            font-family: sans-serif;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .content {
            text-align: justify;
            text-indent: 35px;
            margin-bottom: 14px;
        }
        .detail-table {
            width: 90%;
            margin: 15px auto 20px 35px;
            border-collapse: collapse;
        }
        .detail-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .signature-block {
            margin-top: 40px;
            float: right;
            width: 250px;
            text-align: left;
        }
        .signature-space {
            height: 70px;
        }
        .action-bar {
            max-width: 210mm;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            .action-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <button onclick="window.history.back()" style="padding: 8px 16px; background: #334155; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 12px;">
            &larr; Kembali
        </button>
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 12px;">
            &#128438; Cetak Surat Pengantar
        </button>
    </div>

    <div class="paper">
        <!-- Kop Surat Resmi -->
        <div class="header">
            <h2>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h2>
            <h1>FAKULTAS TEKNIK — UNIVERSITAS</h1>
            <p>Jalan Kampus Terpadu No. 1, Gedung Dekanat Lantai 2, Telepon (021) 555-0199</p>
            <p>Laman: www.teknik.ac.id | Pos-el: dekanat@teknik.ac.id</p>
        </div>

        <!-- Meta Surat -->
        <table class="meta-table">
            <tr>
                <td style="width: 120px;">Nomor</td>
                <td style="width: 15px;">:</td>
                <td><strong>{{ $internship->reference_letter_number ?? '421/FT-TU/MAGANG/' . date('Y') }}</strong></td>
                <td style="text-align: right;">{{ ($internship->reference_letter_issued_at ?? now())->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>1 (satu) Berkas Proposal</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Permohonan Izin Magang / Praktik Kerja Mahasiswa</strong></td>
                <td></td>
            </tr>
        </table>

        <!-- Tujuan Surat -->
        <div style="margin-bottom: 20px;">
            Yth. Pimpinan / HRD / Bagian Diklat<br>
            <strong>{{ $internship->partnerInstitution->name }}</strong><br>
            {{ $internship->partnerInstitution->address }}
        </div>

        <!-- Isi Surat -->
        <p class="content">
            Dengan hormat, sehubungan dengan kurikulum pembelajaran dan persyaratan kelulusan akademik mahasiswa Fakultas Teknik dalam program Praktik Kerja / Magang Mahasiswa, kami bermaksud mengajukan permohonan pelaksanaan magang bagi mahasiswa kami pada instansi/perusahaan yang Bapak/Ibu pimpin.
        </p>

        <p style="margin-bottom: 6px;">Adapun data mahasiswa yang kami rekomendasikan adalah sebagai berikut:</p>

        <table class="detail-table">
            <tr>
                <td style="width: 180px;">Nama Lengkap</td>
                <td style="width: 20px;">:</td>
                <td><strong>{{ $internship->student_name }}</strong></td>
            </tr>
            <tr>
                <td>Nomor Induk Mahasiswa</td>
                <td>:</td>
                <td>{{ $internship->student_nim }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>{{ $internship->studyProgram->name ?? 'Teknik Informatika' }} ({{ $internship->studyProgram->degree_level ?? 'S1' }})</td>
            </tr>
            <tr>
                <td>Periode Pelaksanaan</td>
                <td>:</td>
                <td>{{ $internship->start_date->translatedFormat('d F Y') }} s/d {{ $internship->end_date->translatedFormat('d F Y') }}</td>
            </tr>
            @if ($internship->proposal_title)
            <tr>
                <td>Rencana Topik Magang</td>
                <td>:</td>
                <td>{{ $internship->proposal_title }}</td>
            </tr>
            @endif
        </table>

        <p class="content">
            Besar harapan kami Bapak/Ibu dapat memberikan kesempatan kepada mahasiswa tersebut untuk melaksanakan magang dan menimba pengalaman kerja praktis di {{ $internship->partnerInstitution->name }}. Sebagai bahan pertimbangan, terlampir proposal dan dokumen pendukung mahasiswa yang bersangkutan.
        </p>

        <p class="content">
            Demikian surat permohonan ini kami sampaikan. Atas perhatian, kerja sama, dan kesempatan yang diberikan, kami mengucapkan terima kasih.
        </p>

        <!-- Tanda Tangan Dekanat / Tata Usaha -->
        <div class="signature-block">
            a.n. Dekan<br>
            Wakil Dekan Bidang Akademik,<br>
            <div class="signature-space">
                <!-- Signature / QR Placeholder -->
                <div style="font-family: sans-serif; font-size: 8pt; color: #64748b; border: 1px dashed #94a3b8; padding: 4px; border-radius: 4px; width: 140px; text-align: center; margin-top: 10px;">
                    [TERVALIDASI ELEKTRONIK]<br>
                    ID: #FT-{{ str_pad($internship->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </div>
            <strong>Dr. Ir. Rahmat Hidayat, M.Sc.</strong><br>
            NIP. 197404121999031002
        </div>
    </div>
</body>
</html>
