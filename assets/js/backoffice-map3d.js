// backoffice-map3d.js
// 3D Map for BackOffice Dashboard

// List of gestions with their display name, route, and 3D position
const gestions = window.gestions;

// Basic Three.js setup
const container = document.getElementById('map3d');
const width = container.offsetWidth;
const height = container.offsetHeight;
const scene = new THREE.Scene();
scene.background = new THREE.Color(0xfafcff);
const camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
camera.position.set(0, 30, 40);
camera.lookAt(0, 0, 0);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(width, height);
container.appendChild(renderer.domElement);

// Add a simple ground plane
const planeGeometry = new THREE.PlaneGeometry(40, 40);
const planeMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff, opacity: 0.92, transparent: true });
const plane = new THREE.Mesh(planeGeometry, planeMaterial);
plane.rotation.x = -Math.PI / 2;
scene.add(plane);

// Add a grid helper for clarity
const gridHelper = new THREE.GridHelper(120, 24, 0xcccccc, 0xe0e0e0);
gridHelper.position.y = 0.01;
scene.add(gridHelper);

// Add light
const light = new THREE.DirectionalLight(0xffffff, 1.2);
light.position.set(0, 50, 50);
scene.add(light);

// Add ambient light for softer look
const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
scene.add(ambientLight);

// Store icon meshes for raycasting
const iconMeshes = [];

// Store building meshes for raycasting
const buildingMeshes = [];

// Replace spheres with colored spheres and hover effect
const normalColor = 0x2196f3;
const hoverColor = 0xff9800;
let hoveredMesh = null;

// Building definitions (positions match gestions)
const buildingColors = [0x90caf9, 0xa5d6a7, 0xffcc80, 0xce93d8, 0xffab91, 0xb0bec5, 0xffecb3, 0xc5e1a5];

// Gestion icon image paths (relative to public/)
const iconImagePaths = [
  '/images/map-icons/calendar.png',   // Rendez-vous
  '/images/map-icons/doctor.png',     // Médecins
  '/images/map-icons/gear.png',       // Services
  '/images/map-icons/arrow.png',      // Trajets
  '/images/map-icons/ticket.png',     // Réservations
  '/images/map-icons/package.png',    // Produits
  '/images/map-icons/cart.png',       // Commandes
  '/images/map-icons/blog.png',       // Blog
];

// --- Realistic city block: buildings and streets ---

// City grid parameters
const cityRows = 6;
const cityCols = 8;
const buildingSpacing = 10;
const cityOrigin = { x: -35, z: -25 };
const cityBuildings = [];

// Generate a grid of buildings
for (let row = 0; row < cityRows; row++) {
  for (let col = 0; col < cityCols; col++) {
    // Random height for realism
    const boxHeight = 3 + Math.random() * 8;
    const color = buildingColors[(row * cityCols + col) % buildingColors.length];
    const boxGeometry = new THREE.BoxGeometry(5, boxHeight, 5);
    const boxMaterial = new THREE.MeshStandardMaterial({ color, roughness: 0.4, metalness: 0.2 });
    const box = new THREE.Mesh(boxGeometry, boxMaterial);
    box.position.set(
      cityOrigin.x + col * buildingSpacing,
      boxHeight / 2,
      cityOrigin.z + row * buildingSpacing
    );
    scene.add(box);
    cityBuildings.push(box);
  }
}

// Add streets (horizontal and vertical)
const streetWidth = 2.2;
const streetColor = 0x757575;
for (let row = 0; row <= cityRows; row++) {
  // Horizontal streets
  const streetGeometry = new THREE.BoxGeometry(
    cityCols * buildingSpacing + 5,
    0.2,
    streetWidth
  );
  const streetMaterial = new THREE.MeshStandardMaterial({ color: streetColor, opacity: 0.95, transparent: true });
  const street = new THREE.Mesh(streetGeometry, streetMaterial);
  street.position.set(
    cityOrigin.x + (cityCols - 1) * buildingSpacing / 2,
    0.11,
    cityOrigin.z + (row - 0.5) * buildingSpacing
  );
  scene.add(street);
}
for (let col = 0; col <= cityCols; col++) {
  // Vertical streets
  const streetGeometry = new THREE.BoxGeometry(
    streetWidth,
    0.2,
    cityRows * buildingSpacing + 5
  );
  const streetMaterial = new THREE.MeshStandardMaterial({ color: streetColor, opacity: 0.95, transparent: true });
  const street = new THREE.Mesh(streetGeometry, streetMaterial);
  street.position.set(
    cityOrigin.x + (col - 0.5) * buildingSpacing,
    0.11,
    cityOrigin.z + (cityRows - 1) * buildingSpacing / 2
  );
  scene.add(street);
}

// --- Gestion icons above main buildings (custom PNG sprites for each gestion) ---
iconMeshes.length = 0;
buildingMeshes.length = 0;

// Place gestion icons on the first N buildings in the grid, spaced out further
const gestionBuildingIndexes = [0, 7, 10, 17, 22, 29, 39, 47]; // Spread out more in the bigger grid
for (let i = 0; i < gestions.length; i++) {
  const building = cityBuildings[gestionBuildingIndexes[i]];
  if (!building) continue;
  buildingMeshes.push(building);
  // Icon (custom image sprite) above building
  const texture = new THREE.TextureLoader().load(iconImagePaths[i % iconImagePaths.length]);
  const material = new THREE.SpriteMaterial({ map: texture, color: 0xffffff });
  const sprite = new THREE.Sprite(material);
  sprite.scale.set(5, 5, 1); // Make icon bigger
  sprite.position.set(
    building.position.x,
    building.position.y + (building.geometry.parameters.height / 2) + 3.5,
    building.position.z
  );
  sprite.userData = { url: gestions[i].url, name: gestions[i].name };
  scene.add(sprite);
  iconMeshes.push(sprite);
  // Add a floating label (HTML)
  const label = document.createElement('div');
  label.textContent = gestions[i].name;
  label.className = 'map3d-label';
  container.appendChild(label);
  sprite.userData.label = label;
}

// Raycaster for detecting clicks and hover
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();

function onClick(event) {
  const rect = renderer.domElement.getBoundingClientRect();
  mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  // Check both icons and buildings
  const intersects = raycaster.intersectObjects([...iconMeshes, ...buildingMeshes]);
  if (intersects.length > 0) {
    const mesh = intersects[0].object;
    // If building, find corresponding gestion
    if (buildingMeshes.includes(mesh)) {
      const idx = buildingMeshes.indexOf(mesh);
      if (gestions[idx]) window.location.href = gestions[idx].url;
    } else if (iconMeshes.includes(mesh)) {
      window.location.href = mesh.userData.url;
    }
  }
}
renderer.domElement.addEventListener('click', onClick);

function onPointerMove(event) {
  const rect = renderer.domElement.getBoundingClientRect();
  mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  // Check both icons and buildings for hover
  const intersects = raycaster.intersectObjects([...iconMeshes, ...buildingMeshes]);
  if (hoveredMesh && iconMeshes.includes(hoveredMesh)) hoveredMesh.material.color.set(normalColor);
  if (intersects.length > 0) {
    const mesh = intersects[0].object;
    if (iconMeshes.includes(mesh)) {
      hoveredMesh = mesh;
      hoveredMesh.material.color.set(hoverColor);
      container.style.cursor = 'pointer';
    } else if (buildingMeshes.includes(mesh)) {
      container.style.cursor = 'pointer';
      hoveredMesh = null;
    }
  } else {
    hoveredMesh = null;
    container.style.cursor = 'default';
  }
}
renderer.domElement.addEventListener('pointermove', onPointerMove);

// --- Cars on the streets ---
const carMeshes = [];
const carColors = [0xd32f2f, 0x1976d2, 0x388e3c, 0xfbc02d, 0x7b1fa2, 0x00796b];
const carPaths = [];
const carsPerStreet = 2;

// Define horizontal street paths
for (let row = 0; row < cityRows; row++) {
  const z = cityOrigin.z + row * buildingSpacing;
  carPaths.push({
    from: { x: cityOrigin.x - 6, z },
    to: { x: cityOrigin.x + (cityCols - 1) * buildingSpacing + 6, z },
    dir: 'horizontal',
  });
}
// Define vertical street paths
for (let col = 0; col < cityCols; col++) {
  const x = cityOrigin.x + col * buildingSpacing;
  carPaths.push({
    from: { x, z: cityOrigin.z - 6 },
    to: { x, z: cityOrigin.z + (cityRows - 1) * buildingSpacing + 6 },
    dir: 'vertical',
  });
}

// Create cars
carPaths.forEach((path, i) => {
  for (let c = 0; c < carsPerStreet; c++) {
    const carLength = 2.2, carWidth = 1.2, carHeight = 0.8;
    const carGeometry = new THREE.BoxGeometry(carLength, carHeight, carWidth);
    const carMaterial = new THREE.MeshStandardMaterial({ color: carColors[(i + c) % carColors.length] });
    const car = new THREE.Mesh(carGeometry, carMaterial);
    car.castShadow = true;
    car.userData = {
      path,
      t: Math.random(), // random start position
      speed: 0.08 + Math.random() * 0.04 * (Math.random() > 0.5 ? 1 : -1),
    };
    scene.add(car);
    carMeshes.push(car);
  }
});

// --- People (spheres/capsules) walking near buildings ---
const peopleMeshes = [];
const peopleColors = [0x212121, 0x1565c0, 0x43a047, 0xfbc02d, 0xe64a19, 0x6d4c41];
const peoplePerBuilding = 2;

buildingMeshes.forEach((building, i) => {
  for (let p = 0; p < peoplePerBuilding; p++) {
    // Use capsule if available, else sphere
    let geometry;
    if (THREE.CapsuleGeometry) {
      geometry = new THREE.CapsuleGeometry(0.35, 0.7, 8, 16);
    } else {
      geometry = new THREE.SphereGeometry(0.45, 16, 16);
    }
    const material = new THREE.MeshStandardMaterial({ color: peopleColors[(i + p) % peopleColors.length] });
    const person = new THREE.Mesh(geometry, material);
    // Place people randomly around the building
    const angle = Math.random() * Math.PI * 2;
    const radius = 3.2 + Math.random() * 1.5;
    person.position.set(
      building.position.x + Math.cos(angle) * radius,
      0.45,
      building.position.z + Math.sin(angle) * radius
    );
    person.userData = {
      baseX: person.position.x,
      baseZ: person.position.z,
      angle,
      radius,
      speed: 0.008 + Math.random() * 0.006 * (Math.random() > 0.5 ? 1 : -1),
    };
    scene.add(person);
    peopleMeshes.push(person);
  }
});

// Animate and update label positions, add camera orbit, animate cars and people
let angle = 0;
function map3dAnimate() {
  requestAnimationFrame(map3dAnimate);
  angle += 0.0005;
  camera.position.x = 20 * Math.sin(angle);
  camera.position.z = 40 * Math.cos(angle);
  camera.lookAt(0, 0, 0);
  // Animate gestion icons (sprites)
  iconMeshes.forEach((sprite, idx) => {
    const vector = sprite.position.clone().project(camera);
    const x = (vector.x * 0.5 + 0.5) * width;
    const y = (-vector.y * 0.5 + 0.5) * height;
    sprite.userData.label.style.left = `${x - 30}px`;
    sprite.userData.label.style.top = `${y - 30}px`;
    sprite.position.y = buildingMeshes[idx].position.y + (buildingMeshes[idx].geometry.parameters.height / 2) + 4 + Math.sin(angle * 2 + idx) * 0.5;
  });
  // Animate cars
  carMeshes.forEach(car => {
    car.userData.t += car.userData.speed * 0.008;
    if (car.userData.t > 1) car.userData.t -= 1;
    if (car.userData.t < 0) car.userData.t += 1;
    const { from, to, dir } = car.userData.path;
    const t = car.userData.t;
    if (dir === 'horizontal') {
      car.position.x = from.x + (to.x - from.x) * t;
      car.position.z = from.z;
      car.rotation.y = 0;
      if (car.userData.speed < 0) car.rotation.y = Math.PI;
    } else {
      car.position.x = from.x;
      car.position.z = from.z + (to.z - from.z) * t;
      car.rotation.y = Math.PI / 2;
      if (car.userData.speed < 0) car.rotation.y = -Math.PI / 2;
    }
    car.position.y = 0.5;
  });
  // Animate people (walk in a small circle around their building)
  peopleMeshes.forEach(person => {
    person.userData.angle += person.userData.speed;
    person.position.x = person.userData.baseX + Math.cos(person.userData.angle) * person.userData.radius * 0.3;
    person.position.z = person.userData.baseZ + Math.sin(person.userData.angle) * person.userData.radius * 0.3;
    person.position.y = 0.45 + Math.abs(Math.sin(person.userData.angle * 2)) * 0.08;
  });
  renderer.render(scene, camera);
}
map3dAnimate();

// Responsive resize
window.addEventListener('resize', () => {
  const width = container.offsetWidth;
  const height = container.offsetHeight;
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
  renderer.setSize(width, height);
});
