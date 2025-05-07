// seatmap3d/src/main.js
import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';

// 1) Création de la scène, de la caméra et du renderer
const scene    = new THREE.Scene();
const camera   = new THREE.PerspectiveCamera(
  45, window.innerWidth / window.innerHeight, 1, 1000
);
camera.position.set(0, 2, 10);
camera.lookAt(0, 0, 0);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

// 2) Lumières
const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 1.2);
hemiLight.position.set(0, 20, 0);
scene.add(hemiLight);

const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
dirLight.position.set(5, 10, 7.5);
scene.add(dirLight);

// 3) Chargement du modèle glTF
const loader = new GLTFLoader();
loader.load(
  '../models/vehicle-interior.glb',    // Adapté au chemin depuis src/
  gltf => {
    const vehicle = gltf.scene;
    // Ajustez l’échelle/position si besoin
    vehicle.scale.set(1.2, 1.2, 1.2);
    vehicle.position.set(0, -1, 0);
    scene.add(vehicle);
  },
  xhr => console.log(`Modèle chargé : ${(xhr.loaded/xhr.total*100).toFixed(1)}%`),
  err => console.error('Erreur de chargement glTF :', err)
);

// 4) Raycaster pour cliquer sur les sièges
const raycaster = new THREE.Raycaster();
const mouse     = new THREE.Vector2();

window.addEventListener('click', event => {
  // Normaliser la position de la souris de −1 à +1
  mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
  mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

  raycaster.setFromCamera(mouse, camera);
  // true pour parcourir tous les enfants du modèle
  const hits = raycaster.intersectObjects(scene.children, true);
  if (hits.length > 0) {
    const seat = hits[0].object;
    console.log('Siège cliqué :', seat.name);
    // exemple : tourner le siège en rouge
    if (seat.material) seat.material.color.set(0xff0000);
  }
});

// 5) Boucle de rendu
function animate() {
  requestAnimationFrame(animate);
  renderer.render(scene, camera);
}
animate();

// 6) Adapter au redimensionnement de la fenêtre
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});
