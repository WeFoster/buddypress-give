#BuddyPress EDD Supporters

BuddyPress EDD Supporters is a plugin created for CFCommunity.net: A Social Network for people affected by Cystic Fibrosis (read more here: http://igg.me/at/cfcommunity/x). With BP-EDD-Supporters we want to make it as easy as possible to accept donations for your cause through easy digital downloads. 

## Requirements

- BuddyPress
- Easy Digital Downloads
- Paypal Payments Standard (included with EDD)

### Recommended EDD Extensions
- Recurring Payments
- Stripe
- Product Variations 
- Custom Pricing

## Why do we need this plugin?

CFCommunity is dependant on donations of our users and to collect funding after the initial launch of our community, we needed a (recurring) donation system. There are several options available, but Easy Digital Downloads seems to be the most flexible and well  supported solution. 


# Development Plan

One possible way of setting up  donations would be:

- A Product is created called "Support"
- User chooses the product variation with the right (recurring) donation amount
- User checks out with PayPal or Stripe
- Once the subscription is added a BuddyPress user_meta key is added.
- Use this to query for a custom member loop and display all the user that are supporting the site.
- Once a user updates or cancels it’s donation, the is_support meta key get’s changed to “no”

## Caveats / Notes

- Allow the user to donate anonymously 

## Optional

- Show it when a friend of a user becomes a supporter (through activity stream entry).
- Allow a non-registered visitor to donate and then present him the option to be visible or hidden after the donation.
- Send an email when a (recurring) donation is about to expire
- Show the donations that have been made on a users profile (members/bowe/donations/

