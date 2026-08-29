export function spacePhotoUrl(filename, size = 'thumb') {
  if (!filename) {
    return '';
  }

  if (size === 'full') {
    return `/storage/spaces/${filename}`;
  }

  return `/storage/spaces/thumbs/${filename}`;
}

export function matePhotoUrl(photoPath, size = 'thumb') {
  if (!photoPath) {
    return '';
  }

  if (photoPath.startsWith('data:') || photoPath.startsWith('blob:')) {
    return photoPath;
  }

  if (size === 'full' || !photoPath.includes('/storage/mates/')) {
    return photoPath;
  }

  return photoPath.replace('/storage/mates/', '/storage/mates/thumbs/');
}
