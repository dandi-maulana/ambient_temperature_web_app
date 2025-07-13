from datetime import datetime
import socket
import mysql.connector
from mysql.connector import Error
import time  # Tambahan untuk timer

# Konfigurasi IP dan Port UDP
UDP_IP = "192.168.173.129"
UDP_PORT = 11000

# Inisialisasi socket UDP
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.bind((UDP_IP, UDP_PORT))

# Timer: mulai hitung waktu
start_time = time.time()
duration = 20 * 60  # 20 menit dalam detik

# Koneksi ke database
try:
    connection = mysql.connector.connect(
        host='localhost',
        database='db_temperature',
        user='root',
        password=''
    )

    if connection.is_connected():
        print("Berhasil terhubung ke database")
        print("Menerima data sensor selama 20 menit...\n")

        while True:
            current_time = time.time()
            if current_time - start_time > duration:
                print("Waktu 20 menit telah berakhir. Pengambilan data dihentikan.")
                break

            remaining_time = duration - (current_time - start_time)
            sock.settimeout(remaining_time)

            try:
                data, addr = sock.recvfrom(1024)  # buffer size: 1024 bytes
                print("Data dari IoT:", data)

                try:
                    # Decode dan parsing data
                    dummyString = data.decode("utf-8")
                    dataArray = dummyString.split(';')

                    if len(dataArray) >= 2:
                        idperangkat = dataArray[0]
                        suhu = dataArray[1]

                        print("ID Perangkat:", idperangkat)
                        print("Suhu:", suhu)

                        # Waktu sekarang
                        now = datetime.now()
                        formatted_date = now.strftime('%Y-%m-%d %H:%M:%S')

                        # Simpan ke database
                        cursor = connection.cursor()
                        sql = "INSERT INTO tbl_kantin VALUES (%s, %s, %s, %s)"
                        val = (0, idperangkat, suhu, formatted_date)
                        cursor.execute(sql, val)
                        connection.commit()
                        cursor.close()
                    else:
                        print("Data tidak valid:", dummyString)

                except Exception as e:
                    print("Terjadi kesalahan saat memproses data:", e)

            except socket.timeout:
                print("Tidak ada data diterima dalam sisa waktu. Menghentikan proses.")
                break

except Error as e:
    print("Gagal terkoneksi ke database:", e)

finally:
    if 'connection' in locals() and connection.is_connected():
        connection.close()
        print("Koneksi database ditutup")
    sock.close()
    print("Socket ditutup")
