<?php
$hostname = 'localhost';
$dbname = 'web_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$hostname; dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "koneksi gagal: " . $e->getMessage();
}

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['nim'])) {
            getByNim();
        } else {
            getMahasiswa();
        }
        break;

    case 'POST':
        createMahasiswa();
        break;

    case 'DELETE':
        del();
        break;

    case 'PUT':
        update();
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'SALAH PUH!!!']);
        break;
}

function getMahasiswa(): void
{
    global $pdo;

    $stmt = $pdo->prepare( "SELECT * FROM mahasiswa");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    $json = json_encode($data);
    echo $json;

    
}
function getByNim(): void
{
    global $pdo;
    $nim = $_GET['nim'];

    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["message" => "sukses", "data" => $data]);
}

function createMahasiswa()
{
    global $pdo;

    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $jurusan = $_POST['jurusan'];
    $tahun_masuk = $_POST['tahun_masuk'];

    $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, jurusan, tahun_masuk) VALUES(?,?,?,?)");
    $stmt->execute([$nim, $nama, $jurusan, $tahun_masuk]);

    http_response_code(201);
    echo json_encode(["message" => "Sukses"]);
}

function del()
{
    global $pdo;

    $nim = $_GET['nim'];

    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($data) {
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE nim = ?");
        $stmt->execute([$nim]);

        //Cek Response
        http_response_code(200);
        echo json_encode(["message" => "Terhapus", "data" => $data]);
        
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Nim Tidak Ditemukan"]);
    }
}

function update()
{
    global $pdo;
    parse_str(string: file_get_contents(filename: "php://input"), result: $_PUT);
    $param_nim = $_GET['nim'] ?? null;
    $nim = $_PUT['nim'] ?? null;
    $nama = $_PUT['nama'] ?? null;
    $jurusan = $_PUT['jurusan'] ?? null;
    $tahun_masuk = $_PUT['tahun_masuk'] ?? null;


    // Ambil data JSON dari body request

    if ($param_nim && $nim && $nama && $jurusan && $tahun_masuk) {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET nim = ?,nama = ?,jurusan = ?,tahun_masuk = ? WHERE nim = ?");
        $stmt->execute([$nim, $nama, $jurusan, $tahun_masuk, $param_nim]);


        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
        $stmt->execute([$nim]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message" => "sukses", "data" => $data]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "gagal", "data" => $data]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Format data tidak lengkap"]);
    }
}
