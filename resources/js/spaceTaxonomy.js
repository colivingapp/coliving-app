export const CATEGORY_META = {
  'managed-coliving': {
    label: 'Managed Coliving',
    description: 'Professionally managed coliving with shared areas and services, typically offered by an operator.',
  },
  'shared-housing': {
    label: 'Shared Housing',
    description: 'Roommate-style housing where people share a home or apartment, often renting individual rooms.',
  },
  'student-housing': {
    label: 'Student Housing',
    description: 'Housing primarily aimed at students, such as residences or student-focused apartments.',
  },
  'digital-nomad': {
    label: 'Digital Nomad Coliving',
    description: 'Coliving designed for remote workers and travelers, usually with a strong work setup and community.',
  },
  'cohousing': {
    label: 'Cohousing / Communal Living',
    description: 'Intentional communities where residents co-create shared spaces, routines, and responsibilities.',
  },
  'senior-coliving': {
    label: 'Senior Coliving',
    description: 'Shared living for older adults focused on community, comfort, and optional support services.',
  },
  'short-term': {
    label: 'Short-Term Coliving',
    description: 'Temporary community living such as retreats, pop-ups, seasonal stays, or project-based houses.',
  },
};

export const CATEGORY_ORDER = [
  'managed-coliving',
  'shared-housing',
  'student-housing',
  'digital-nomad',
  'cohousing',
  'senior-coliving',
  'short-term',
];

export const SUBCATEGORY_META = {
  'managed-coliving': {
    'private-rooms': {
      label: 'Private rooms',
      description: 'Mostly private bedrooms with shared kitchens and common areas.',
    },
    'studios-micro-units': {
      label: 'Studios / micro-units',
      description: 'Mostly self-contained studios or small units within a managed community.',
    },
    'mixed-units': {
      label: 'Mixed units',
      description: 'A mix of private rooms, studios, and/or apartments in one space.',
    },
    'serviced-apartments': {
      label: 'Serviced apartments',
      description: 'Apartment-style units with hotel-like services (cleaning, linen, reception, etc.).',
    },
    'corporate-housing': {
      label: 'Corporate Housing',
      description: 'Housing focused on business stays, relocations, or company accommodation.',
    },
    'subscription-membership': {
      label: 'Subscription / membership model',
      description: 'Access via membership or subscription rather than a traditional fixed lease.',
    },
  },
  'shared-housing': {
    'flat-apartment': {
      label: 'Flat / Apartment',
      description: 'Shared apartment-style living (roommates sharing one unit).',
    },
    'house-villa': {
      label: 'House / Villa',
      description: 'Shared house-style living (roommates sharing one property).',
    },
    'private-room': {
      label: 'Private Room',
      description: 'A private bedroom in a shared home (shared kitchen/bathroom may apply).',
    },
    'shared-room': {
      label: 'Shared Room',
      description: 'A shared bedroom arrangement (e.g., multiple beds in one room).',
    },
  },
  'student-housing': {
    'university-residence': {
      label: 'University Residence',
      description: 'Dorms or university-managed residences, often near or on campus.',
    },
    'private-student-housing': {
      label: 'Private Student Housing',
      description: 'Student-focused housing run by private operators.',
    },
    'exchange-erasmus': {
      label: 'Exchange / Erasmus',
      description: 'Housing commonly used by exchange or Erasmus students.',
    },
    'researcher-phd': {
      label: 'Researcher / PhD',
      description: 'Housing aimed at researchers, PhD students, or academic stays.',
    },
  },
  'digital-nomad': {
    'work-first': {
      label: 'Work-first',
      description: 'Optimized for focused remote work (coworking, desks, fast internet).',
    },
    'community-first': {
      label: 'Community-first',
      description: 'Designed around social connection, events, and community routines.',
    },
    'builder-house': {
      label: 'Builder house',
      description: 'Startup / founder / builder-focused community and collaboration.',
    },
    'travel-first': {
      label: 'Travel-first',
      description: 'More oriented to experiences, exploration, and location-based activities.',
    },
  },
  'cohousing': {
    'cohousing-formal': {
      label: 'Cohousing (Formal)',
      description: 'Resident-led community with shared design, governance, and common facilities.',
    },
    'commune-informal': {
      label: 'Commune (Informal)',
      description: 'Informal communal living with shared resources and collaborative routines.',
    },
    'eco-community': {
      label: 'Eco Community',
      description: 'Community living with a focus on sustainability and ecological practices.',
    },
    'intentional-community': {
      label: 'Intentional Community',
      description: 'Community formed around shared values, lifestyle, or a common mission.',
    },
  },
  'senior-coliving': {
    'independent-living': {
      label: 'Independent Living',
      description: 'Independent shared living with light services and community support.',
    },
    'assisted-living': {
      label: 'Assisted Living',
      description: 'Shared living with care or assistance services available.',
    },
    'intergenerational': {
      label: 'Intergenerational',
      description: 'Living that mixes age groups and encourages intergenerational connection.',
    },
    '55-community': {
      label: '55+ Community',
      description: 'Age-restricted community aimed at residents 55 and older.',
    },
  },
  'short-term': {
    'seasonal': {
      label: 'Seasonal',
      description: 'Operates around seasons (e.g., summer/winter houses).',
    },
    'retreat-based': {
      label: 'Retreat-Based',
      description: 'Short stays centered around retreats, programs, or shared experiences.',
    },
    'pop-up': {
      label: 'Pop-Up',
      description: 'Temporary pop-up houses that run for a limited time.',
    },
    'project-based': {
      label: 'Project-Based',
      description: 'Temporary living built around a project, residency, or focused collaboration.',
    },
  },
};

export function getCategoryMeta(slug) {
  return CATEGORY_META[slug] || null;
}

export function getCategoryDescription(slug) {
  return CATEGORY_META[slug]?.description || '';
}

export function getSubcategoryMeta(categorySlug, subcategorySlug) {
  return SUBCATEGORY_META[categorySlug]?.[subcategorySlug] || null;
}

export function getSubcategoryDescription(categorySlug, subcategorySlug) {
  return SUBCATEGORY_META[categorySlug]?.[subcategorySlug]?.description || '';
}
