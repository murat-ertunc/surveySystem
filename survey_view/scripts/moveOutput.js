// const fs = require('fs-extra');
// const path = require('path');

// const buildDir = path.join(__dirname, '../out');
// const targetDir = path.join(__dirname, '../../survey-app/public');

// async function moveBuild() {
//   try {
//     await fs.remove(targetDir);

//     await fs.move(buildDir, targetDir);

//     console.log(`Build başarıyla ${targetDir} dizinine taşındı.`);
//   } catch (err) {
//     console.error('Build taşınırken hata oluştu:', err);
//     process.exit(1);
//   }
// }

// moveBuild();


const fs = require('fs');
const path = require('path');

const buildDir = path.join(__dirname, '../out'); // Build edilen dosyaların olduğu klasör
const targetDir = path.join(__dirname, '../../survey-app/public');
const oldDir = path.join(__dirname, '../../survey-app/public_old'); // Eski klasör adı

function moveBuild() {
  if (fs.existsSync(targetDir)) {
    // Hedef klasörü 'public_old' olarak yeniden adlandır
    if (fs.existsSync(oldDir)) {
      fs.rmSync(oldDir, { recursive: true, force: true }); // Eğer public_old zaten varsa, sil
    }
    fs.renameSync(targetDir, oldDir); // public'i public_old olarak yeniden adlandır
    console.log('public klasörü public_old olarak yeniden adlandırıldı.');
  }

  // Build klasörünü 'public' dizinine taşı
  fs.renameSync(buildDir, targetDir);
  console.log(`Build başarıyla ${targetDir} dizinine taşındı.`);

  // public_old klasöründeki dosyaları geri taşı
  const files = fs.readdirSync(oldDir);
  files.forEach(file => {
    const oldPath = path.join(oldDir, file);
    const newPath = path.join(targetDir, file);
    if (!fs.existsSync(newPath)) {
      fs.renameSync(oldPath, newPath); // Eski klasördeki dosyaları yeni public dizinine taşı
    }
  });

  // public_old klasörünü sil
  fs.rmSync(oldDir, { recursive: true, force: true });
  console.log('public_old klasörü silindi.');
}

try {
  moveBuild();
} catch (err) {
  console.error('Build taşınırken hata oluştu:', err);
  process.exit(1);
}

