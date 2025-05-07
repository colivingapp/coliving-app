## Map Components Overview

- `Map.vue`:  
  Wrapper for Leaflet or Google Maps integration. Acts as the base container for all interactive maps.

- `MapHome.vue`:  
  Used on the homepage to display a simplified, zoomed-out preview of coliving spaces globally.

- `MapPopup.vue`:  
  Displays a simple popup with space details (name, location, link) when a marker is clicked.

- `MapSpacesSearchable.vue`:  
  Full-featured interactive map showing all spaces as markers. Used on the `/spaces` page and supports zooming, dragging, and dynamic marker updates. Integrates with filters and search UI if needed.

- `MapByCountry.vue`:  
  Renders a map focused on a specific country, displaying only spaces within that region.