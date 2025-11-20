<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Voters',
        href: '/voters',
    },
];
</script>

<template>
  <Head title="Voter Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Voter Status</h1>
        <p class="text-gray-600 mt-1">View Voter Status.</p>
      </div>

      <!-- Controls Card -->
      <section class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <!-- Search -->
          <div class="flex items-center flex-1 bg-slate-50 rounded-lg px-3 py-2 gap-2">
            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input type="search" placeholder="Search voter name, student ID" class="bg-transparent w-full text-sm placeholder:text-gray-400 focus:outline-none" />
          </div>

          <!-- Filters -->
          <div class="flex gap-2 items-center">
            <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
              <option>All Years</option>
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>

            <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
              <option>Program</option>
              <option>Information Technology</option>
              <option>Information System</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Table Card -->
      <section class="bg-white rounded-xl shadow-sm p-4">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100">
            <thead>
              <tr class="text-left text-sm text-slate-600">
                <th class="py-3 pr-4 font-semibold">Name</th>
                <th class="py-3 pr-4 font-semibold">Student ID</th>
                <th class="py-3 pr-4 font-semibold">Program</th>
                <th class="py-3 pr-4 font-semibold">Year</th>
                <th class="py-3 pr-4 font-semibold">Status</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
              <!-- Sample Row 1 -->
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 max-w-xs truncate">Alex Mendoza</td>
                <td class="py-3 pr-4">2023001</td>
                <td class="py-3 pr-4 max-w-xs truncate">Information Technology</td>
                <td class="py-3 pr-4">2</td>
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 rounded-full mr-2 bg-green-500"></span>
                    Voted
                  </span>
                </td>
            
              </tr>

              <!-- Sample Row 2 -->
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 max-w-xs truncate">Jamie Cruz</td>
                <td class="py-3 pr-4">2023002</td>
                <td class="py-3 pr-4 max-w-xs truncate">Information Technology</td>
                <td class="py-3 pr-4">3</td>
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                    <span class="w-2 h-2 rounded-full mr-2 bg-slate-400"></span>
                    Not Voted
                  </span>
                </td>
              </tr>

              <!-- Sample Row 3 -->
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 max-w-xs truncate">Taylor Reyes</td>
                <td class="py-3 pr-4">2023003</td>
                <td class="py-3 pr-4 max-w-xs truncate">Infomation Technology</td>
                <td class="py-3 pr-4">4</td>
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                    <span class="w-2 h-2 rounded-full mr-2 bg-slate-400"></span>
                    Not Voted
                  </span>
                </td>
              </tr>

               <!-- Sample Row 4 -->
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 max-w-xs truncate">Taylor Reyes</td>
                <td class="py-3 pr-4">2023003</td>
                <td class="py-3 pr-4 max-w-xs truncate">Infomation Technology</td>
                <td class="py-3 pr-4">4</td>
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-200 text-slate-700">
                    <span class="w-2 h-2 rounded-full mr-2 bg-yellow-400"></span>
                    Pending
                  </span>
                </td>
              </tr>
              
              <!-- Sample Row 5 -->
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 max-w-xs truncate">Alex Mendoza</td>
                <td class="py-3 pr-4">2023001</td>
                <td class="py-3 pr-4 max-w-xs truncate">Information Technology</td>
                <td class="py-3 pr-4">2</td>
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 rounded-full mr-2 bg-green-500"></span>
                    Voted
                  </span>
                </td>
              </tr>

            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-end gap-2">
          <nav class="inline-flex items-center gap-1" role="navigation" aria-label="Pagination">
            <a href="#" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Previous</a>
            <a href="#" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">1</a>
            <a href="#" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-slate-800 text-white">2</a>
            <a href="#" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">3</a>
            <a href="#" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Next</a>
          </nav>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
