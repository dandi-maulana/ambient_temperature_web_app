import socket
import time

# Konfigurasi IP dan PORT
UDP_IP = "192.168.173.129"
UDP_PORT = 11000

# Membuat socket UDP
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.bind((UDP_IP, UDP_PORT))

# Catat waktu mulai dan durasi maksimal (20 menit)
start_time = time.time()
duration = 20 * 60  # 20 menit = 1200 detik

print("Mulai menerima data sensor selama 20 menit...\n")

try:
    while True:
        current_time = time.time()
        elapsed_time = current_time - start_time

        # Hentikan jika sudah lebih dari 20 menit
        if elapsed_time > duration:
            print("Waktu 20 menit telah berakhir. Pengambilan data dihentikan.")
            break

        # Set timeout agar recvfrom tidak menggantung
        remaining_time = duration - elapsed_time
        sock.settimeout(remaining_time)

        try:
            data, addr = sock.recvfrom(1024)  # buffer 1024 bytes
            print("Data dari IoT:", data)

            dummyString = data.decode("utf-8")
            dataArray = dummyString.split(';')

            if len(dataArray) >= 2:
                idperangkat = dataArray[0]
                suhu = dataArray[1]
                print("ID Perangkat:", idperangkat)
                print("Suhu:", suhu)
            else:
                print("Format data tidak valid:", dummyString)

        except socket.timeout:
            print("Tidak ada data diterima dalam sisa waktu. Menghentikan proses.")
            break

finally:
    sock.close()
    print("Socket ditutup.")
