export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  profile_photo?: string;
  bio?: string;
  status: string;
  roles: { role: string }[];
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface Event {
  id: number;
  title_en: string;
  title_ar?: string;
  slug: string;
  description_en: string;
  description_ar?: string;
  start_date: string;
  end_date: string;
  category: string;
  cover_image?: string;
  tags?: string[];
  status: string;
  has_tickets: boolean;
  is_free: boolean;
  view_count: number;
  attending_count: number;
  save_count: number;
  venue?: { id: number; name_en: string; name_ar?: string; city: string };
  organizer?: { id: number; name_en: string; name_ar?: string };
  ticket_types?: TicketType[];
  min_price?: number;
}

export interface TicketType {
  id: number;
  event_id: number;
  name_en: string;
  name_ar?: string;
  description_en?: string;
  description_ar?: string;
  price: number;
  currency: string;
  quantity_total: number;
  quantity_sold: number;
  status: string;
}

export interface Ticket {
  id: number;
  ticket_code: string;
  order_id: number;
  event_id: number;
  event?: { title_en: string };
  holder_name: string;
  holder_email: string;
  price_paid: number;
  currency: string;
  qr_code_url?: string;
  status: string;
  checked_in: boolean;
}

export interface Order {
  id: number;
  order_number: string;
  quantity: number;
  total_amount: number;
  currency: string;
  status: string;
  created_at: string;
  tickets: Ticket[];
}

export interface Organizer {
  id: number;
  name_en: string;
  name_ar?: string;
  slug: string;
  logo?: string;
  verified: boolean;
  status: string;
  followers_count?: number;
}

export interface Venue {
  id: number;
  name_en: string;
  name_ar?: string;
  slug: string;
  city: string;
  capacity?: number;
  cover_image?: string;
  amenities?: string[];
}
